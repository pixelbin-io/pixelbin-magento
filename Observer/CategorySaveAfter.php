<?php

namespace Pixelbinio\Pixelbin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;

class CategorySaveAfter extends AbstractObserver
{
    /**
     * Catalog category save after observer
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->isModuleEnabled()) {
            /**
             * @var Category $category
             */
            $category = $observer->getEvent()->getCategory();
            $imageUrl = $category->getImageUrl();
            $this->uploadFileToPixelbin->categoryUploadFileSync($imageUrl);
        }
    }
}
