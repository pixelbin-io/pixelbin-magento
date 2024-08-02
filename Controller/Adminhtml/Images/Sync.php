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

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Images;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\MediaStorage\Model\File\Storage as StorageModel;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncStatus;

class Sync extends Action
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StorageModel
     */
    protected $storageModel;

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * Sync construct
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param StorageModel $storageModel
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        StorageModel $storageModel,
        UploadFileToPixelbin $uploadFileToPixelbin
    )
    {
        $this->helperData = $helperData;
        $this->storageModel = $storageModel;
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        parent::__construct($context);
    }

    /**
     * Sync execute method
     *
     * @return void
     */
    public function execute()
    {
        try {
            $sourceModel = $this->storageModel->getStorageModel();
            $offset = 0;
            while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
                $eachFileData = [];
                foreach ($files as $file) {
                    unset($file["content"]);
                    $folderMatchCount = 0;
                    str_replace(HelperData::EXCLUDE_FOLDERS, '', (string)$file["directory"], $folderMatchCount);
                    if ($folderMatchCount > 0) {
                        $this->helperData->logData("EXCLUDE_FOLDERS directory found => " . $file["directory"]);
                        continue;
                    }
                    $pathInfo = $this->uploadFileToPixelbin->getPathInfo($file["filename"]);
                    if (in_array($pathInfo["extension"], HelperData::EXCLUDE_EXTENSION)) {
                        $this->helperData->logData("EXCLUDE_EXTENSION found => " . $pathInfo["extension"]);
                        continue;
                    }
                    $fileName = ltrim($file["directory"] . "/" . $file["filename"], "/");
                    $syncCollection = $this->uploadFileToPixelbin->getSyncCollection($fileName);
                    if ($syncCollection->getSize() > 0) {
                        continue;
                    }
                    $file["full_path"] = $fileName;
                    $eachFileData[] = $file;
                    $saveData = [
                        PixelbinSynchronisationInterface::KEY_IMAGE_PATH => $file["full_path"],
                        PixelbinSynchronisationInterface::KEY_FILE_DATA => json_encode($eachFileData),
                        PixelbinSynchronisationInterface::KEY_SYNC_STATUS => SyncStatus::STATUS_PENDING,
                    ];
                    $this->uploadFileToPixelbin->saveSynchronisationLogs($saveData);
                }
                $offset += count($files);
            }
            $this->messageManager->addSuccessMessage(
                __("Sync Collection added in queue")
            );
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage(
                __($ex->getMessage())
            );
            $this->helperData->logData("error exception while manual sync.", [], "error");
            $this->helperData->logData($ex->getMessage(), [], "error");
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
