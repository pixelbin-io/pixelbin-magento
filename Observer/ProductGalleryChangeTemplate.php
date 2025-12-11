<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
