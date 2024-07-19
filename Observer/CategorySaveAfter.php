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
use Magento\Catalog\Model\Category;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;

class CategorySaveAfter implements \Magento\Framework\Event\ObserverInterface
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
     * Catalog category save after observer
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Category $category
         */
        $category = $observer->getEvent()->getCategory();
        $imageUrl = $category->getImageUrl();
        $this->uploadFileToPixelbin->categoryUploadFileSync($imageUrl);
    }
}
