<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Ajax;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Pixelbin\Utils\Url;
use Pixelbinio\Pixelbin\Model\Framework\File\Uploader;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config as ProductMediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File as FileUtility;
use Magento\PageBuilder\Controller\Adminhtml\ContentType\Image\Upload as PageBuilderContentTypeUpload;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\PixelbinHelperData as PixelbinHelperData;
use Magento\Framework\Filesystem\Driver\File;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveImage extends \Magento\Backend\App\Action implements CsrfAwareActionInterface
{
    /**
     * @var string|null
     */
    private $remoteFileUrl;

    /**
     * @var bool
     */
    private $usingPlaceholderFallback = false;

    /**
     * @var array
     */
    private $parsedRemoteFileUrl = [];

    /**
     * @var string|null
     */
    private $cldUniqid;

    /**
     * @var ResultRawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ProductMediaConfig
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var ImageAdapterFactory
     */
    protected $imageAdapter;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var FileUtility
     */
    protected $fileUtility;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * AllowedProtocols validator
     *
     * @var AllowedProtocols
     */
    private $protocolValidator;

    /**
     * @var NotProtectedExtension
     */
    private $extensionValidator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var PixelbinHelperData
     */
    private $pixelbinHelperData;

    /**
     * @var File
     */
    protected File $fileDriver;

    /**
     * @var FileIo
     */
    protected $fileIo;

    /**
     * @method __construct
     * @param Context $context
     * @param ResultRawFactory $resultRawFactory
     * @param ProductMediaConfig $mediaConfig
     * @param Filesystem $fileSystem
     * @param ImageAdapterFactory $imageAdapterFactory
     * @param Curl $curl
     * @param FileUtility $fileUtility
     * @param FileProcessor $fileProcessor
     * @param AllowedProtocols $protocolValidator
     * @param NotProtectedExtension $extensionValidator
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param PixelbinHelperData $pixelbinHelperData
     * @param File $fileDriver
     * @param FileIo $fileIo
     */
    public function __construct(
        Context $context,
        ResultRawFactory $resultRawFactory,
        ProductMediaConfig $mediaConfig,
        Filesystem $fileSystem,
        ImageAdapterFactory $imageAdapterFactory,
        Curl $curl,
        FileUtility $fileUtility,
        FileProcessor $fileProcessor,
        AllowedProtocols $protocolValidator,
        NotProtectedExtension $extensionValidator,
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        PixelbinHelperData $pixelbinHelperData,
        File $fileDriver,
        FileIo $fileIo
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->fileProcessor = $fileProcessor;
        $this->extensionValidator = $extensionValidator;
        $this->protocolValidator = $protocolValidator;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->pixelbinHelperData = $pixelbinHelperData;
        $this->fileDriver = $fileDriver;
        $this->fileIo = $fileIo;
    }

    /**
     * Create CSRF validation exception
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate for CSRF
     *
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Retriever image execute
     *
     * @return Raw
     * @throws FileSystemException
     */
    public function execute()
    {
        $allData = $this->getRequest()->getParams();
        try {
            $localUniqFilePath = $this->remoteFileUrl = $allData["asset"]["url"];
            $fileData = $this->fileIo->getPathInfo($localUniqFilePath);
            if (!isset($fileData["extension"])) {
                $extension = $allData["asset"]["format"];
                $localUniqFilePath = $this->remoteFileUrl = $localUniqFilePath . "." . strtolower($extension);
            }
            $imageData = $this->helperData->validatePixelbinUrl($localUniqFilePath, true);
            if (is_array($imageData)) {
                $result = [
                    'error' => $imageData["message"] ?? __("Something went wrong while retrieving image."),
                    'errorcode' => "400",
                    'trace' => ""
                ];
            } else {
                $this->validateRemoteFile($this->remoteFileUrl);
                $this->parsedRemoteFileUrl = $this->pixelbinHelperData->parsePixelbinUrl($this->remoteFileUrl);
                $this->parsedRemoteFileUrl["transformations_string"] = $allData['asset']["free_transformation"];
                $assetParsedRemoteFileUrl = $this->pixelbinHelperData->parsePixelbinUrl($localUniqFilePath);
                $this->parsedRemoteFileUrl["type"] = $assetParsedRemoteFileUrl['type'];
                $this->parsedRemoteFileUrl["thumbnail_url"] = $assetParsedRemoteFileUrl['thumbnail_url'];
                $baseTmpMediaPath = $this->getBaseTmpMediaPath();
                $localUniqFilePath = $this->appendNewFileName(
                    $baseTmpMediaPath . $this->getLocalTmpFileName($localUniqFilePath)
                );
                $this->validateFileExtensions($localUniqFilePath);
                $this->retrieveRemoteImage($this->remoteFileUrl, $localUniqFilePath);
                $localFileFullPath = $this->appendAbsoluteFileSystemPath($localUniqFilePath);
                $this->imageAdapter->validateUploadFile($localFileFullPath);
                $result = $this->appendResultSaveRemoteImage($localUniqFilePath, $baseTmpMediaPath);
            }
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
            $fileWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (isset($localFileFullPath) && $fileWriter->isExist($localFileFullPath)) {
                $fileWriter->delete($localFileFullPath);
            }
        }
        /** @var Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * Get Base temp media path
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getBaseTmpMediaPath()
    {
        $baseTmpMediaPath = false;
        switch ($this->getRequest()->getParam('type')) {
            case 'design_config_fileUploader':
                $baseTmpMediaPath = 'tmp/' . FileProcessor::FILE_DIR;
                break;
            case 'pagebuilder_contenttype':
                $baseTmpMediaPath = PageBuilderContentTypeUpload::UPLOAD_DIR;
                break;
            case 'category_image':
                $baseTmpMediaPath = 'catalog/tmp/category';
                break;
            default:
                $baseTmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
                break;
        }
        if (!$baseTmpMediaPath) {
            throw new LocalizedException(__("Empty baseTmpMediaPath"));
        }
        return $baseTmpMediaPath;
    }

    /**
     * Get Local tmp file name
     *
     * @param string $remoteFileUrl
     * @return string
     */
    protected function getLocalTmpFileName($remoteFileUrl)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $localFileName = Uploader::getCorrectFileName(basename($remoteFileUrl));
        $fileData = $this->fileIo->getPathInfo($localFileName);
        if ($fileData["extension"] == "mp4") {
            $localFileName = $fileData["filename"] . ".png";
        }
        switch ($this->getRequest()->getParam('type')) {
            case 'pagebuilder_contenttype':
            case 'design_config_fileUploader':
            case 'category_image':
                $localTmpFileName = DIRECTORY_SEPARATOR . $localFileName;
                break;
            default:
                $localTmpFileName = Uploader::getDispretionPath($localFileName) . DIRECTORY_SEPARATOR . $localFileName;
                break;
        }
        return $localTmpFileName;
    }

    /**
     * Validate remote file
     *
     * @throws LocalizedException
     *
     * @return $this
     */
    private function validateRemoteFile()
    {
        if (!$this->protocolValidator->isValid($this->remoteFileUrl)) {
            throw new LocalizedException(
                __("Protocol isn't allowed")
            );
        }
        return $this;
    }

    /**
     * Invalidates files that have script extensions.
     *
     * @param string $filePath
     * @throws ValidatorException
     * @return void
     */
    private function validateFileExtensions($filePath)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$this->extensionValidator->isValid($extension)) {
            throw new ValidatorException(__('Disallowed file type.'));
        }
    }

    /**
     * Append result save remove image
     *
     * @param string $localUniqFilePath
     * @param string $baseTmpMediaPath
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function appendResultSaveRemoteImage($localUniqFilePath, $baseTmpMediaPath)
    {
        $tmpFileName = $localUniqFilePath;
        if (substr($tmpFileName, 0, strlen($baseTmpMediaPath)) == $baseTmpMediaPath) {
            $tmpFileName = substr($tmpFileName, strlen($baseTmpMediaPath));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result['name'] = basename($localUniqFilePath);
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result['size'] = filesize($this->appendAbsoluteFileSystemPath($localUniqFilePath));
        $result['url'] = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $localUniqFilePath;
        $result['tmp_name'] = $this->appendAbsoluteFileSystemPath($localUniqFilePath);
        $result['file'] = $tmpFileName;
        $result['using_placeholder_fallback'] = (bool) $this->usingPlaceholderFallback;

        return $result;
    }

    /**
     * Trying to get remote image to save it locally
     *
     * @param string $fileUrl
     * @param string $localFilePath
     * @return void
     * @throws LocalizedException
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        if ($this->getRequest()->getParam('asset')["assetType"] === 'video') {
            //Fallback for video thumbnail image, use placeholder or store logo
            $this->usingPlaceholderFallback = true;
            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::APP)
                ->getAbsolutePath();
            $defaultImage = $mediaDirectory . HelperData::DEFAULT_PIXELBIN_IMAGE;
            $image = $this->fileDriver->fileGetContents($defaultImage);
        }

        if (empty($image)) {
            $this->usingPlaceholderFallback = false;
            throw new LocalizedException(
                __('The preview image information is unavailable. Check your connection and try again.')
            );
        }
        $this->fileUtility->saveFile($localFilePath, $image);
    }

    /**
     * Append new file name
     *
     * @param string $localFilePath
     * @return string
     */
    protected function appendNewFileName($localFilePath)
    {
        $destinationFile = $this->appendAbsoluteFileSystemPath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileInfo = pathinfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Append absolute file system path
     *
     * @param string $localTmpFile
     * @return string
     * @throws ValidatorException
     */
    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave . $localTmpFile;
    }

    /**
     * Get Placeholder url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPlaceholderUrl()
    {
        $configPaths = [
            'catalog/placeholder/image_placeholder',
            'catalog/placeholder/small_image_placeholder',
            'catalog/placeholder/thumbnail_placeholder',
        ];
        foreach ($configPaths as $configPath) {
            if (($path = $this->storeManager->getStore()->getConfig($configPath))) {
                return $this->storeManager->getStore()
                        ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/' . $path;
            }
        }
        return $this->_view->getLayout()->createBlock(\Magento\Theme\Block\Html\Header\Logo::class)
            ->getViewFileUrl('Pixelbinio_Pixelbin::images/pixelbin_logo_light.png');
    }
}
