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

class ProductSaveAfter extends AbstractObserver
{
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

        if ($this->helperData->isModuleEnabled()) {
            $images = $product->getMediaGalleryImages();
            foreach ($images as $image) {
                $this->uploadFileToPixelbin->catalogUploadFileSync($image->getData());
            }
        }

        $imageTypes = ['product_base_image', 'product_small_image', 'product_thumbnail_image'];
        foreach ($imageTypes as $imageType) {
            try {
                $this->imageHelper->init($product, $imageType)
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->resize(300)
                    ->getUrl();
            } catch (\Exception $ex) {
                $this->helperData->logData("Exception while generating image cache ".$ex->getMessage());
            }
        }
    }
}
