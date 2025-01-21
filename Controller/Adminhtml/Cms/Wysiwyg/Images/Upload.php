<?php
/**
 * Iksula
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Pixelbinio
 * @package     Pixelbinio_Pixelbin
 * @version     1.0.0
 */

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Cms\Wysiwyg\Images;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
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

    /**
     * @var MediaGalleryUploader
     */
    private $mediaGalleryUploader;

    /**
     * @var AssetInterfaceFactory
     */
    protected $mediaAsset;

    /**
     * @var SaveAssetsInterface
     */
    protected $mediaAssetSave;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * @method __construct
     * @param Context $context
     * @param Registry $coreRegistry
     * @param JsonFactory $resultJsonFactory
     * @param DirectoryResolver|null $directoryResolver
     * @param DirectoryList $directoryList
     * @param Config $mediaConfig
     * @param Filesystem $fileSystem
     * @param AdapterFactory $imageAdapterFactory
     * @param Curl $curl
     * @param File $fileUtility
     * @param AllowedProtocols $protocolValidator
     * @param NotProtectedExtension $extensionValidator
     * @param HelperData $helperData
     * @param MediaGalleryUploader $mediaGalleryUploader
     * @param AssetInterfaceFactory $mediaAsset
     * @param SaveAssetsInterface $mediaAssetSave
     * @param Logger $logger
     * @param UploadFileToPixelbin $uploadFileToPixelbin
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
        Logger $logger,
        UploadFileToPixelbin $uploadFileToPixelbin

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
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
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

            if (!$path) {
                $path = $this->directoryList->getRoot() .'/pub/'. DirectoryList::MEDIA .'/';
            }

            if (!$this->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            $allData = $this->getRequest()->getParams();
            if (!empty($allData["target_path"])) {
                $path = $path . $allData["target_path"]."/";
            }
            $localFileName = $this->remoteFileUrl = $allData["asset"]["url"];
            $imagePathArray = explode($this->helperData->getAppZone(), $localFileName);
            $this->validateRemoteFile($this->remoteFileUrl);
            $this->parsedRemoteFileUrl = $this->helperData->parsePixelbinUrl($this->remoteFileUrl);

            $this->parsedRemoteFileUrl["transformations_string"] = $allData['asset']["free_transformation"];


            $localFileName = Uploader::getCorrectFileName(basename($allData["asset"]["name"]));
            //$extraPathName = explode($localFileName, $imagePathArray[1]);
            $extraPathName = pathinfo($allData["asset"]["name"], PATHINFO_EXTENSION);;

            $localFilePath = $this->appendNewFileName($path . $extraPathName . $localFileName);
            $this->validateRemoteFileExtensions($localFilePath);

            $this->retrieveRemoteImage($this->remoteFileUrl, $localFilePath);
            $this->getStorage()->resizeFile($localFilePath, true);
            $this->imageAdapter->validateUploadFile($localFilePath);
            $result = $this->appendResultSaveRemoteImage($localFilePath);
            $this->syncImageWithPixelbin($result);
            $asset = $allData['asset'];
            $reg = preg_match('/^(.*)\/media\//', $localFilePath, $substruct);
            $newPath = str_replace($substruct[0], '', $localFilePath);
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
     * Sync image with pixelbin
     *
     * @param array $result
     * @return void
     */
    private function syncImageWithPixelbin($result)
    {
        $targetPath = explode("/", $result['file']);
        array_pop($targetPath);

        $fileData = [
            "name" => $result['name'],
            "full_path" => $result['name'],
            "size" => $result['size'],
            "path" => implode("/", $targetPath),
            "file" => $result['name'],
        ];
        $this->uploadFileToPixelbin->cmsUploadFileSync($fileData);
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
     * Append a new file name
     *
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
     * Append an absolute file system path
     *
     * @param $localTmpFile
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave . $localTmpFile;
    }
}
