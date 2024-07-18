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

namespace Pixelbinio\Pixelbin\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
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
        HelperData $helperData
    ) {
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->helperData = $helperData;
    }

    /**
     * Catalog product save after observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $images = $product->getMediaGalleryImages();
        foreach ($images as $image) {
            $this->uploadFileToPixelbin->catalogUploadFileSync($image->getData());
        }
    }
}
