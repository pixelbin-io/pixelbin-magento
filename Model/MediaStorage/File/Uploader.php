<?php

namespace Pixelbinio\Pixelbin\Model\MediaStorage\File;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;

class Uploader
{
    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     * @param HelperData $helperData
     */
    public function __construct(
        UploadFileToPixelbin $uploadFileToPixelbin,
        HelperData           $helperData
    ) {
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->helperData = $helperData;
    }

    /**
     * Add web images to the list ollowed extension for media storage
     *
     * @param \Magento\MediaStorage\Model\File\Uploader $uploader
     * @param array $extensions
     * @return array
     */
    public function beforeSetAllowedExtensions(FileUploader $uploader, $extensions = [])
    {
        $extensions = array_merge(
            $extensions,
            array_values($this->helperData->getVectorExtensions()),
            array_values($this->helperData->getWebImageExtensions())
        );

        return [$extensions];
    }

    /**
     * After image Save
     *
     * @param \Magento\MediaStorage\Model\File\Uploader $subject
     * @param array $result
     * @return array
     */
    public function afterSave(FileUploader $subject, $result)
    {
        $this->uploadFileToPixelbin->fileUploadAfterSave($result);
        return $result;
    }
}
