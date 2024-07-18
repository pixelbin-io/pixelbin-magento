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
use Magento\Framework\Filesystem\DriverInterface;

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
     * @var DriverInterface
     */
    protected $driver;

    /**
     * Cache key for media directory absolute path
     *
     * @var string
     */
    protected $mediaDir = "";

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
     * @param DriverInterface $driver
     * @param PixelbinImageSyncLogsFactory $pixelbinImageSyncLogsFactory
     * @param PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
     * @param PixelbinSynchronisationFactory $pixelbinSynchronisationFactory
     */
    public function __construct(
        Context                        $context,
        Data                           $helperData,
        FileIo                         $fileIo,
        Filesystem                     $filesystem,
        DriverInterface                $driver,
        PixelbinImageSyncLogsFactory   $pixelbinImageSyncLogsFactory,
        PixelbinSyncCollectionFactory  $pixelbinSyncCollectionFactory,
        PixelbinSynchronisationFactory $pixelbinSynchronisationFactory
    ) {
        $this->helperData = $helperData;
        $this->fileIo = $fileIo;
        $this->filesystem = $filesystem;
        $this->driver = $driver;
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
        $this->helperData->logData("file data => ".json_encode($file));
        try {
            $pixelbin = $this->getPixelbinObj();
            $result = $pixelbin->assets->fileUpload(
                $this->driver->fileOpen($file["absolute_path"], "r"),
                $file["path_folder"],
                $file["file_name"],
                AccessEnum::PUBLIC_READ,
                $file["tags"] ?? [],
                null,
                true,
                false
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
                $this->pixelbinSynchronisationFactory->create()
                    ->setData([
                        PixelbinSynchronisationInterface::KEY_IMAGE_PATH => $file["full_path"]
                    ])->save();
            }
            return $result;
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __($ex->getMessage())
            );
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
        foreach ($files as $file) {
            try {
                unset($file["content"]);
                if ($file["filename"] == "LICENSE.txt") {
                    continue;
                }
                $pathInfo = $this->fileIo->getPathInfo($file["filename"]);
                $file["file_name"] = $pathInfo["filename"];
                $fileName = ltrim($file["directory"] . "/" . $file["filename"], "/");
                $syncCollection = $this->pixelbinSyncCollectionFactory->create()
                    ->addFieldToFilter(PixelbinSynchronisationInterface::KEY_IMAGE_PATH, $fileName);
                if ($syncCollection->getSize() > 0) {
                    continue;
                }
                $mediaDir = $this->getMediaAbsolutePath();
                $file["absolute_path"] = $mediaDir . $fileName;
                $filePathArr = explode("/", $fileName);
                array_pop($filePathArr);
                $file["path_folder"] = implode('/', $filePathArr);
                $file["full_path"] = $fileName;
                $fileUploadResult = $this->uploadFile($file, $syncType);
                $this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
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
        ];
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
            $fileName = "catalog/product".$file["file"];
            $filePathArr = explode("/", $fileName);
            array_pop($filePathArr);
            $file["path_folder"] = implode('/', $filePathArr);
            $file["full_path"] = $fileName;
            $pathInfo = $this->fileIo->getPathInfo($file["file"]);
            $file["file_name"] = $pathInfo["filename"];
            $file["filename"] = $pathInfo["basename"];
            $fileUploadResult = $this->uploadFile($file, SyncType::TYPE_MANUAL);
            $this->helperData->logData("response from pixelbin is => ", $fileUploadResult);
        } catch (\Exception $ex) {
            $this->helperData->logData("file upload exception here => " . $ex->getMessage());
        }
        return true;
    }
}
