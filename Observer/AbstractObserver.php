<?php

namespace Pixelbinio\Pixelbin\Observer;

use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Catalog\Helper\Image as ImageHelper;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
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
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     * @param HelperData $helperData
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        UploadFileToPixelbin $uploadFileToPixelbin,
        HelperData $helperData,
        ImageHelper $imageHelper
    ) {
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->helperData = $helperData;
        $this->imageHelper = $imageHelper;
    }
}
