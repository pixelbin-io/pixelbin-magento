<?php

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Cms\Wysiwyg\Images;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Model\Framework\File\Uploader;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryResolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;
use Magento\MediaGalleryUi\Model\UploadImage as MediaGalleryUploader;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Pixelbinio\Pixelbin\Logger\Logger;

/**
 * Upload image.
 */
class Upload extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Upload
{
    /**
     * @var string|null
     */
    private $remoteFileUrl;

    /**
     * @var array
     */
    private $parsedRemoteFileUrl = [];

    /**
     * @var string|null
     */
    private $cldUniqid;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var AbstractAdapter
     */
    protected $imageAdapter;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var File
     */
    protected $fileUtility;

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
     * @var HelperData
     */
    private $helperData;

    private $mediaGalleryUploader;

    protected $mediaAsset;

    protected $mediaAssetSave;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @method __construct
     * @param  Context                $context
     * @param  Registry               $coreRegistry
     * @param  JsonFactory            $resultJsonFactory
     * @param  DirectoryResolver|null $directoryResolver
     * @param  DirectoryList          $directoryList
     * @param  Config                 $mediaConfig
     * @param  Filesystem             $fileSystem
     * @param  AdapterFactory         $imageAdapterFactory
     * @param  Curl                   $curl
     * @param  File                   $fileUtility
     * @param  AllowedProtocols       $protocolValidator
     * @param  NotProtectedExtension  $extensionValidator
     * @param  HelperData $helperData
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        JsonFactory $resultJsonFactory,
        DirectoryResolver $directoryResolver = null,
        DirectoryList $directoryList,
        Config $mediaConfig,
        Filesystem $fileSystem,
        AdapterFactory $imageAdapterFactory,
        Curl $curl,
        File $fileUtility,
        AllowedProtocols $protocolValidator,
        NotProtectedExtension $extensionValidator,
        HelperData $helperData,
        MediaGalleryUploader $mediaGalleryUploader,
        AssetInterfaceFactory $mediaAsset,
        SaveAssetsInterface $mediaAssetSave,
        Logger $logger

    ) {
        parent::__construct($context, $coreRegistry, $resultJsonFactory, $directoryResolver);
        $this->directoryList = $directoryList;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->extensionValidator = $extensionValidator;
        $this->protocolValidator = $protocolValidator;
        $this->helperData = $helperData;
        $this->mediaGalleryUploader = $mediaGalleryUploader;
        $this->mediaAsset = $mediaAsset;
        $this->mediaAssetSave = $mediaAssetSave;
        $this->logger = $logger;
    }

    /**
     * Files upload processing.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $path = ($this->getStorage()->getSession()->getCurrentPath()) ?? null;

            if (!$path){
                $path = $this->directoryList->getRoot() .'/pub/'. DirectoryList::MEDIA .'/';
            }

            if (!$this->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            $allData = $this->getRequest()->getParams();
            
            $localFileName = $this->remoteFileUrl = $allData["asset"]["url"];
            $imagePathArray = explode($this->helperData->getAppZone(), $localFileName);
            $this->validateRemoteFile($this->remoteFileUrl);
            $this->parsedRemoteFileUrl = $this->helperData->parsePixelbinUrl($this->remoteFileUrl);
            
            $this->parsedRemoteFileUrl["transformations_string"] = $allData['asset']["free_transformation"];
            
            
            $localFileName = Uploader::getCorrectFileName(basename($imagePathArray[1]));
            $extraPathName = explode($localFileName, $imagePathArray[1]);
            
            $localFilePath = $this->appendNewFileName($path . $extraPathName[0] . $localFileName);
            $this->validateRemoteFileExtensions($localFilePath);
            

            $this->retrieveRemoteImage($this->remoteFileUrl, $localFilePath);
            $this->getStorage()->resizeFile($localFilePath, true);
            $this->imageAdapter->validateUploadFile($localFilePath);
            $result = $this->appendResultSaveRemoteImage($localFilePath);
            
            $asset = $allData['asset'];
            $reg = preg_match('/^(.*)\/media\//',$localFilePath,$substruct);
            $newPath = str_replace($substruct[0],'',$localFilePath);
            $ma = $this->mediaAsset->create(
                [
                    'path' => $newPath,
                    'description' => $localFileName,
                    'contentType' => $asset['assetType'].'/'.$asset['format'],
                    'title' => $localFileName,
                    'source' => 'Pixelbin',
                    'width' => $asset['width'],
                    'height' => $asset['height'],
                    'size' => $asset['size']
                ]
            );
            $this->mediaAssetSave->execute([$ma]);
            
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
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
     * Validate path.
     *
     * Gets real path for directory provided in parameters and compares it with specified root directory.
     * Will return TRUE if real path of provided value contains root directory path and FALSE if not.
     * Throws the \Magento\Framework\Exception\FileSystemException in case when directory path is absent
     * in Directories helperData.
     *
     * @param string $path
     * @param string $directoryConfig
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function validatePath($path, $directoryConfig = DirectoryList::MEDIA)
    {
        $directory = $this->fileSystem->getDirectoryWrite($directoryConfig);
        $realPath = $directory->getDriver()->getRealPathSafety($path);
        $root = $this->directoryList->getPath($directoryConfig);

        return strpos($realPath, $root) === 0;
    }

    /**
     * Invalidates files that have script extensions.
     *
     * @param string $filePath
     * @throws \Magento\Framework\Exception\ValidatorException
     * @return void
     */
    private function validateRemoteFileExtensions($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $allowedExtensions = (array) $this->getStorage()->getAllowedExtensions($this->getRequest()->getParam('type'));
        if (!$this->extensionValidator->isValid($extension) || !in_array($extension, $allowedExtensions)) {
            throw new \Magento\Framework\Exception\ValidatorException(__('Disallowed file type.'));
        }
    }

    /**
     * @param string $filePath
     * @return mixed
     */
    protected function appendResultSaveRemoteImage($filePath)
    {
        $fileInfo = pathinfo($filePath);
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($filePath);
        $result['url'] = $this->getRequest()->getParam('remote_image');
        $result['file'] = $filePath;
        return $result;
    }

    /**
     * Trying to get remote image to save it locally
     *
     * @param string $fileUrl
     * @param string $localFilePath
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        if (empty($image)) {
            throw new LocalizedException(
                __('The preview image information is unavailable. Check your connection and try again.')
            );
        }
        $this->fileUtility->saveFile($localFilePath, $image);
    }

    /**
     * @param string $localFilePath
     * @return string
     */
    protected function appendNewFileName($localFilePath)
    {
        $fileName = Uploader::getNewFileName($localFilePath);
        $fileInfo = pathinfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $localTmpFile
     * @return string
     */
    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave . $localTmpFile;
    }

}
