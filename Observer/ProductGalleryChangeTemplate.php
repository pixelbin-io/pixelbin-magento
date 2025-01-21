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

use Magento\Framework\Event\Observer;

class ProductGalleryChangeTemplate extends AbstractObserver
{
    /**
     * Setting a gallery template
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->isModuleEnabled()) {
            $observer->getBlock()->setTemplate('Pixelbinio_Pixelbin::catalog/product/gallery.phtml');
        }
    }
}
