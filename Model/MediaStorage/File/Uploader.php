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

namespace Pixelbinio\Pixelbin\Model\MediaStorage\File;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;

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
    public function beforeSetAllowedExtensions(Uploader $uploader, $extensions = [])
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
    public function afterSave($subject, $result)
    {
        if ($this->helperData->isModuleEnabled()) {
            if (!empty($result) && !empty($result['path']) && !empty($result['file'])) {
                $filePath = $result['path'] . '/' . $result['file'];
                if (!str_contains($filePath, 'tmp/')) {
                    $this->uploadFileToPixelbin->cmsUploadFileSync($result);
                }
            }
        }
        return $result;
    }
}
