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
     * @var Filesystem
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
     * @var string
     */
    protected $mediaDir = "";

    /**
     * @var string
     */
    protected $pubDir = "";

    /**
     * @var null
     */
    protected $pixelbinObj = null;

    /**
     * UploadFileToPixelbin construct
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
     * Create pixelbin obj
     *
     * @return PixelbinClient
     * @throws LocalizedException
     */
    public function getPixelbinObj()
    {
        try {
            if ($this->pixelbinObj === null) {
                $config = new PixelbinConfig([
                    "domain" => $this->helperData->getApiUrl(),
                    "apiSecret" => $this->helperData->getAppApiSecret(),
                ]);
                $this->pixelbinObj = new PixelbinClient(config: $config);
            }
            return $this->pixelbinObj;
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __("Unable to create pixelbin object: " . $ex->getMessage())
            );
        }
    }

    /**
     * Update file to Pixelbin
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
                tags: $file["tags"] ?? [],
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

            $this->helperData->logData("Request for Pixelbin => " . json_encode($requestData));

            $syncLog = [
                PixelbinImageSyncLogsInterface::KEY_REQUEST => json_encode($requestData),
                PixelbinImageSyncLogsInterface::KEY_RESPONSE => json_encode($result),
                PixelbinImageSyncLogsInterface::KEY_SYNC_TYPE => $syncType,
            ];

            $this->pixelbinImageSyncLogsFactory->create()->setData($syncLog)->save();

            if (empty($result["_id"] ?? "")) {
                throw new LocalizedException(__("Unable to upload file to Pixelbin."));
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
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Save log in table
     *
     * @param array $data
     * @return \Pixelbinio\Pixelbin\Model\PixelbinSynchronisation
     * @throws \Exception
     */
    public function saveSynchronisationLogs(array $data)
    {
        $imagePath = $data[PixelbinSynchronisationInterface::KEY_IMAGE_PATH];
        $collection = $this->getSyncCollection($imagePath);

        $model = $this->pixelbinSynchronisationFactory->create()->addData($data);

        if ($collection->getSize() > 0) {
            $model->setId($collection->getFirstItem()->getEntityId());
        }

        return $model->save();
    }

    /**
     * Get media absolute path
     *
     * @return string
     */
    public function getMediaAbsolutePath()
    {
        if (empty($this->mediaDir)) {
            $this->mediaDir = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath();
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
            $this->pubDir = $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath();
        }
        return $this->pubDir;
    }

    /**
     * Import files
     *
     * @param array $files
     * @param string $syncType
     * @return array
     */
    public function importFiles(array $files, string $syncType): array
    {
        $success = $error = [];
        $counts = [
            "success" => 0,
            "error" => 0,
            "excludeFolder" => 0,
            "excludeExtension" => 0
        ];

        foreach ($files as $file) {
            try {
                unset($file["content"]);

                // Skip excluded folders
                $folderMatch = 0;
                str_replace(Data::EXCLUDE_FOLDERS, '', (string)$file["directory"], $folderMatch);
                if ($folderMatch > 0) {
                    $counts["excludeFolder"]++;
                    continue;
                }

                // Skip non-supported extensions
                $pathInfo = $this->getPathInfo($file["filename"]);
                if (!in_array(strtolower($pathInfo["extension"]), Data::ALLOWED_EXTENSION_SYNC)) {
                    $counts["excludeExtension"]++;
                    continue;
                }

                $fileName = ltrim($file["directory"] . "/" . $file["filename"], "/");
                if ($syncType !== SyncType::TYPE_CRON &&
                    $this->getSyncCollection($fileName)->getSize() > 0) {
                    continue;
                }

                $mediaDir = $this->getMediaAbsolutePath();
                $file["absolute_path"] = $mediaDir . $fileName;
                // @codingStandardsIgnoreLine
                $file["path_folder"] = dirname($fileName);
                $file["full_path"] = $fileName;
                $file["file_name"] = $pathInfo["filename"];

                $this->uploadFile($file, $syncType);
                $success[] = __("Uploaded: %1", $fileName);
                $counts["success"]++;
            } catch (\Exception $ex) {
                $error[] = $ex->getMessage();
                $counts["error"]++;
                $this->helperData->logData("Upload exception => " . $ex->getMessage());
            }
        }

        return [
            "success" => $success,
            "errors" => $error,
            "counts" => $counts,
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
        $collection = $this->pixelbinSyncCollectionFactory->create()
            ->addFieldToFilter(PixelbinSynchronisationInterface::KEY_IMAGE_PATH, $fileName);

        if (!empty($status)) {
            $collection->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                $status
            );
        }

        return $collection;
    }

    /**
     * Prepare and upload files
     *
     * @param string $relativePath
     * @param string $basePath
     * @param bool $appendMedia
     * @return bool
     */
    private function prepareAndUploadFile(string $relativePath, string $basePath, bool $appendMedia = false): bool
    {
        try {
            $mediaDir = $this->getMediaAbsolutePath();
            $file = [];
            // Build file data
            $file["absolute_path"] = $appendMedia ? $mediaDir . $relativePath : $basePath . "/" . $relativePath;
            // @codingStandardsIgnoreLine
            $folders = $appendMedia ? dirname($relativePath) : str_replace($mediaDir, "", $basePath);
            // @codingStandardsIgnoreLine
            $fileName = ltrim($folders . "/" . basename($relativePath), "/");
            // @codingStandardsIgnoreLine
            $file["path_folder"] = dirname($fileName);
            $file["full_path"] = $fileName;

            // @codingStandardsIgnoreLine
            $pathInfo = $this->getPathInfo(basename($relativePath));
            $file["file_name"] = $pathInfo["filename"];
            $file["filename"] = $pathInfo["basename"];
            $result = $this->uploadFile($file, SyncType::TYPE_MANUAL);
            $this->helperData->logData("Response from Pixelbin => ", $result);
        } catch (\Exception $ex) {
            $this->helperData->logData("Upload exception => " . $ex->getMessage());
        }

        return true;
    }

    /**
     * Catalog upload file sync
     *
     * @param array $file
     * @return bool
     */
    public function catalogUploadFileSync(array $file): bool
    {
        $relativePath = ltrim("catalog/product" . $file["file"], "/");
        return $this->prepareAndUploadFile($relativePath, $file["path"], true);
    }

    /**
     * Cms upload file sync
     *
     * @param array $file
     * @return bool
     */
    public function cmsUploadFileSync(array $file): bool
    {
        return $this->prepareAndUploadFile($file["file"], $file["path"]);
    }

    /**
     * Category upload file sync
     *
     * @param string $image
     * @return bool
     */
    public function categoryUploadFileSync(string $image): bool
    {
        $relativePath = str_replace("/media", "", $image);
        return $this->prepareAndUploadFile($relativePath, $this->getMediaAbsolutePath(), true);
    }

    /**
     * Get path info
     *
     * @param string $fileName
     * @return mixed
     */
    public function getPathInfo($fileName)
    {
        return $this->fileIo->getPathInfo($fileName);
    }

    /**
     * File upload after save
     *
     * @param array $result
     * @return void
     */
    public function fileUploadAfterSave($result)
    {
        if ($this->helperData->isModuleEnabled()) {
            if (!empty($result) && !empty($result['path']) && !empty($result['file'])) {
                $filePath = $result['path'] . '/' . $result['file'];
                if (!str_contains($filePath, 'tmp/')) {
                    $this->cmsUploadFileSync($result);
                }
            }
        }
    }
}
