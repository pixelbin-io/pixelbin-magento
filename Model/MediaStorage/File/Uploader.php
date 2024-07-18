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
     * After image Save
     *
     * @param \Magento\MediaStorage\Model\File\Uploader $subject
     * @param array $result
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
//        if ($this->helperData->isModuleEnabled()) {
//            if (!empty($result) && !empty($result['path']) && !empty($result['file'])) {
//                $filePath = $result['path'] . '/' . $result['file'];
//                if (strpos($filePath, 'pub/media') !== false) {
//                    $key = substr($filePath, strpos($filePath, 'pub/media/') + strlen('pub/media/'));
//
//                }
//
//                $result['file'] = '/' . $result['file'];
//            }
//        }
        return $result;
    }
}
