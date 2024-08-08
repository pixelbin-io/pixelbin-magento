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

namespace Pixelbinio\Pixelbin\Cron;

use Magento\Framework\DB\Select;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncType;
use Pixelbinio\Pixelbin\Model\PixelbinSynchronisation;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\CollectionFactory as PixelbinSyncCollectionFactory;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncStatus;

class PushImagesToPixelbin
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PixelbinSyncCollectionFactory
     */
    protected $pixelbinSyncCollectionFactory;

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * PushImagesToPixelbin construct
     *
     * @param Logger $logger
     * @param HelperData $helperData
     * @param PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     */
    public function __construct(
        Logger                        $logger,
        HelperData                    $helperData,
        PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory,
        UploadFileToPixelbin          $uploadFileToPixelbin
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->pixelbinSyncCollectionFactory = $pixelbinSyncCollectionFactory;
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
    }

    /**
     * PushImagesToPixelbin execute
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->helperData->isModuleEnabled()) {
            return;
        }
        if (!$this->helperData->isManualSyncCronEnabled()) {
            return;
        }
        try {
            $collection = $this->getCollection();
            if ($collection->getSize()) {
                /**
                 * @var PixelbinSynchronisation $item
                 */
                foreach ($collection as $item) {
                    $fileData = json_decode($item->getFileData(), true);
                    $uploadResponse = $this->uploadFileToPixelbin->importFiles(
                        $fileData,
                        SyncType::TYPE_CRON
                    );
                    if (!empty($uploadResponse["successCount"])) {
                        $item->setSyncStatus(SyncStatus::STATUS_SYNCED);
                        $item->save();
                    }
                    if (!empty($uploadResponse["errorCount"])) {
                        $item->setSyncStatus(SyncStatus::STATUS_ERROR);
                        $item->setErrorMessage(implode(", ", $uploadResponse["errors"]));
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->helperData->logData("Push image to pixelbin exception", [], "error");
            $this->helperData->logData($ex->getMessage(), [], "error");
        }
    }

    /**
     * Get collection from table
     *
     * @return \Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\Collection
     */
    private function getCollection()
    {
        $collection =  $this->pixelbinSyncCollectionFactory->create();
        $collection->addFieldToFilter(
            PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
            [
                    "eq" => SyncStatus::STATUS_PENDING
                ]
        )->setPageSize(HelperData::LIMIT_FOR_CRON)
            ->setCurPage(1)
            ->setOrder(PixelbinSynchronisationInterface::KEY_SYNC_STATUS, "ASC")
            ->load();
        return $collection;
    }
}
