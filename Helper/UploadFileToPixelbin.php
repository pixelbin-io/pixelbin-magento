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

namespace Pixelbinio\Pixelbin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Pixelbin\Platform\PixelbinClient;
use Pixelbin\Platform\PixelbinConfig;
use Pixelbin\Platform\Enums\AccessEnum;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncType;
use Pixelbinio\Pixelbin\Model\PixelbinImageSyncLogsFactory;
use Pixelbinio\Pixelbin\Model\PixelbinSynchronisationFactory;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\CollectionFactory as PixelbinSyncCollectionFactory;
use Pixelbinio\Pixelbin\Api\Data\PixelbinImageSyncLogsInterface;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncStatus;

class UploadFileToPixelbin extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FileIo
     */
    protected $fileIo;

    /**
     * @var Filesystem `
     */
    protected $filesystem;

    /**
     * @var PixelbinImageSyncLogsFactory
     */
    protected $pixelbinImageSyncLogsFactory;

    /**
     * @var PixelbinSyncCollectionFactory
     */
    protected $pixelbinSyncCollectionFactory;

    /**
     * @var PixelbinSynchronisationFactory
     */
    protected $pixelbinSynchronisationFactory;

    /**
     * @var DriverFile
     */
    protected $driverFile;

    /**
     * Cache key for media directory absolute path
     *
     * @var string
     */
    protected $mediaDir = "";

    /**
     * Cache key for pub directory absolute path
     *
     * @var string
     */
    protected $pubDir = "";

    /**
     * Cache key for Pixelbin class object
     *
     * @var null|PixelbinClient
     */
    protected $pixelbinObj = null;

    /**
     * UploadFileToPixelbin Construct
     *
     * @param Context $context
     * @param Data $helperData
     * @param FileIo $fileIo
     * @param Filesystem $filesystem
     * @param DriverFile $driverFile
     * @param PixelbinImageSyncLogsFactory $pixelbinImageSyncLogsFactory
     * @param PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
     * @param PixelbinSynchronisationFactory $pixelbinSynchronisationFactory
     */
    public function __construct(
        Context                        $context,
        Data                           $helperData,
        FileIo                         $fileIo,
        Filesystem                     $filesystem,
        DriverFile                     $driverFile,
        PixelbinImageSyncLogsFactory   $pixelbinImageSyncLogsFactory,
        PixelbinSyncCollectionFactory  $pixelbinSyncCollectionFactory,
        PixelbinSynchronisationFactory $pixelbinSynchronisationFactory
    ) {
        $this->helperData = $helperData;
        $this->fileIo = $fileIo;
        $this->filesystem = $filesystem;
        $this->driverFile = $driverFile;
        $this->pixelbinImageSyncLogsFactory = $pixelbinImageSyncLogsFactory;
        $this->pixelbinSyncCollectionFactory = $pixelbinSyncCollectionFactory;
        $this->pixelbinSynchronisationFactory = $pixelbinSynchronisationFactory;

        parent::__construct($context);
    }

    /**
     * Get pixelbin object
     *
     * @return PixelbinClient
     * @throws LocalizedException
     */
    public function getPixelbinObj()
    {
        try {
            if ($this->pixelbinObj == null) {
                $config = new PixelbinConfig([
                    "domain" => $this->helperData->getApiUrl(),
                    "apiSecret" => $this->helperData->getAppApiSecret(),
                ]);
                $this->pixelbinObj = new PixelbinClient(config: $config);
            }
            return $this->pixelbinObj;
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __("Unable to create pixelbin object error is => " . $ex->getMessage())
            );
        }
    }

    /**
     * Upload file to pixelbin
     *
     * @param array $file
     * @param string $syncType
     * @return array
     * @throws LocalizedException
     */
    public function uploadFile(array $file, string $syncType): array
    {
        try {
            $pixelbin = $this->getPixelbinObj();
            $result = $pixelbin->assets->fileUpload(
                file: $this->driverFile->fileOpen($file["absolute_path"], "r"),
                path: $file["path_folder"],
                access: AccessEnum::PUBLIC_READ,
                tags:$file["tags"] ?? [],
                overwrite: true,
                filenameOverride: true
            );
            $requestData = [
                "path" => $file["path_folder"],
                "name" => $file["filename"],
                "access" => AccessEnum::PUBLIC_READ,
                "tags" => json_encode($file["tags"] ?? []),
                "metadata" => null,
                "overwrite" => true,
                "filenameOverride" => true,
            ];
            $this->helperData->logData("request for pixelbin => ".json_encode($requestData));
            $syncLog = [
                PixelbinImageSyncLogsInterface::KEY_REQUEST => json_encode($requestData),
                PixelbinImageSyncLogsInterface::KEY_RESPONSE => json_encode($result),
                PixelbinImageSyncLogsInterface::KEY_SYNC_TYPE => $syncType,
            ];
            $this->pixelbinImageSyncLogsFactory->create()
                ->setData($syncLog)
                ->save();
            $pixelbinId = $result["_id"] ?? "";
            if (empty($pixelbinId)) {
                throw new LocalizedException(
                    __("Unable to upload file to pixelbin some error is there.")
                );
            }
            if (isset($file["full_path"])) {
                $data = [
                    PixelbinSynchronisationInterface::KEY_IMAGE_PATH => $file["full_path"],
                    PixelbinSynchronisationInterface::KEY_SYNC_STATUS => SyncStatus::STATUS_SYNCED,
                ];
                $this->saveSynchronisationLogs($data);
            }
            return $result;
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __($ex->getMessage())
            );
        }
    }

    /**
     * Save synchronisation logs
     *
     * @param array $data
     * @return \Pixelbinio\Pixelbin\Model\PixelbinSynchronisation
     * @throws \Exception
     */
    public function saveSynchronisationLogs(array $data)
    {
        $imagePath = $data[PixelbinSynchronisationInterface::KEY_IMAGE_PATH];
        $syncedCollection = $this->getSyncCollection($imagePath);
        if ($syncedCollection->getSize() > 0) {
            $syncedModel = $syncedCollection->getFirstItem();
            return $this->pixelbinSynchronisationFactory->create()
                ->addData($data)->setId($syncedModel->getEntityId())->save();
        } else {
            return $this->pixelbinSynchronisationFactory->create()
                ->setData($data)->save();
        }
    }

    /**
     * Get media absolute path
     *
     * @return string
     */
    public function getMediaAbsolutePath()
    {
        if (empty($this->mediaDir)) {
            $this->mediaDir = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath();
        }
        return $this->mediaDir;
    }

    /**
     * Get pub absolute path
     *
     * @return string
     */
    public function getPubAbsolutePath()
    {
        if (empty($this->pubDir)) {
            $this->pubDir = $this->filesystem->getDirectoryRead(
                DirectoryList::PUB
            )->getAbsolutePath();
        }
        return $this->pubDir;
    }

    /**
     * Import file to pixelbin
     *
     * @param array $files
     * @param string $syncType
     * @return array
     */
    public function importFiles(array $files, string $syncType): array
    {
        $success = [];
        $successCounts = [];
        $error = [];
        $errorCounts = [];
        $excludeFolderCounts = [];
        $excludeExtensionCounts = [];
        foreach ($files as $file) {
            try {
                unset($file["content"]);
                $folderMatchCount = 0;
                str_replace(Data::EXCLUDE_FOLDERS, '', (string)$file["directory"], $folderMatchCount);
                if ($folderMatchCount > 0) {
                    $excludeFolderCounts = count($excludeFolderCounts) + 1;
                    $this->helperData->logData("EXCLUDE_FOLDERS directory found => " . $file["directory"]);
                    continue;
                }
                $pathInfo = $this->getPathInfo($file["filename"]);
                if (in_array($pathInfo["extension"], Data::EXCLUDE_EXTENSION)) {
                    $this->helperData->logData("EXCLUDE_EXTENSION found => " . $pathInfo["extension"]);
                    $excludeExtensionCounts = count($excludeExtensionCounts) + 1;
                    continue;
                }
                $file["file_name"] = $pathInfo["filename"];
                $fileName = ltrim($file["directory"] . "/" . $file["filename"], "/");
                if ($syncType != SyncType::TYPE_CRON) {
                    $syncCollection = $this->getSyncCollection($fileName);
                    if ($syncCollection->getSize() > 0) {
                        continue;
                    }
                }
                $mediaDir = $this->getMediaAbsolutePath();
                $file["absolute_path"] = $mediaDir . $fileName;
                $filePathArr = explode("/", $fileName);
                array_pop($filePathArr);
                $file["path_folder"] = implode('/', $filePathArr);
                $file["full_path"] = $fileName;
                $fileUploadResult = $this->uploadFile($file, $syncType);
                //$this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
                $success[] = __("successfully uploaded %1", $fileName);
                $successCounts[] = count($successCounts) + 1;
            } catch (\Exception $ex) {
                $error[] = $ex->getMessage();
                $errorCounts[] = count($errorCounts) + 1;
                $this->helperData->logData("file upload exception here => " . $ex->getMessage());
            }
        }
        return [
            "success" => $success,
            "successCount" => $successCounts,
            "errors" => $error,
            "errorCount" => $errorCounts,
            "excludeFolderCounts" => $excludeFolderCounts,
            "excludeExtensionCounts" => $excludeExtensionCounts,
        ];
    }

    /**
     * Get sync collection
     *
     * @param string $fileName
     * @param string $status
     * @return \Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\Collection
     */
    public function getSyncCollection($fileName, $status = "")
    {
        $syncCollection = $this->pixelbinSyncCollectionFactory->create()
            ->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_IMAGE_PATH,
                $fileName
            );
        if (!empty($status)) {
            $syncCollection->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                $status
            );
        }
        return $syncCollection;
    }

    /**
     * Upload catalog image to pixelbin
     *
     * @param array $file
     * @return bool
     */
    public function catalogUploadFileSync(array $file): bool
    {
        try {
            $file["absolute_path"] = $file["path"];
            $fileName = "catalog/product" . $file["file"];
            $filePathArr = explode("/", $fileName);
            array_pop($filePathArr);
            $file["path_folder"] = implode('/', $filePathArr);
            $file["full_path"] = $fileName;
            $pathInfo = $this->getPathInfo($file["file"]);
            $file["file_name"] = $pathInfo["filename"];
            $file["filename"] = $pathInfo["basename"];
            $fileUploadResult = $this->uploadFile($file, SyncType::TYPE_MANUAL);
            $this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
        } catch (\Exception $ex) {
            $this->helperData->logData("file upload exception here => " . $ex->getMessage());
        }
        return true;
    }

    /**
     * Upload CMS image to pixelbin
     *
     * @param array $file
     * @return bool
     */
    public function cmsUploadFileSync(array $file): bool
    {
        try {
            $file["absolute_path"] = $file["path"] . "/" . $file["file"];
            $mediaDir = $this->getMediaAbsolutePath();
            $folders = str_replace($mediaDir, "", $file["path"]);
            $fileName = $folders . "/" . $file["file"];
            $filePathArr = explode("/", $fileName);
            array_pop($filePathArr);
            $file["path_folder"] = implode('/', $filePathArr);
            $file["full_path"] = $fileName;
            $pathInfo = $this->getPathInfo($file["file"]);
            $file["file_name"] = $pathInfo["filename"];
            $file["filename"] = $pathInfo["basename"];
            $fileUploadResult = $this->uploadFile($file, SyncType::TYPE_MANUAL);
            $this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
        } catch (\Exception $ex) {
            $this->helperData->logData("file upload exception here => " . $ex->getMessage());
        }
        return true;
    }

    /**
     * Upload category image to pixelbin
     *
     * @param string $image
     * @return bool
     */
    public function categoryUploadFileSync(string $image): bool
    {
        try {
            $image = str_replace("/media", "", $image);
            $mediaDir = $this->getMediaAbsolutePath();
            $file["absolute_path"] = $mediaDir . $image;
            $fileName = $image;
            $filePathArr = explode("/", $fileName);
            array_pop($filePathArr);
            $file["path_folder"] = implode('/', $filePathArr);
            $file["full_path"] = $fileName;
            $pathInfo = $this->getPathInfo($fileName);
            $file["file_name"] = $pathInfo["filename"];
            $file["filename"] = $pathInfo["basename"];
            $fileUploadResult = $this->uploadFile($file, SyncType::TYPE_MANUAL);
            $this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
        } catch (\Exception $ex) {
            $this->helperData->logData("file upload exception here => " . $ex->getMessage());
        }
        return true;
    }

    /**
     * Get file path info
     *
     * @param string $fileName
     * @return mixed
     */
    public function getPathInfo($fileName)
    {
        return $this->fileIo->getPathInfo($fileName);
    }
}
