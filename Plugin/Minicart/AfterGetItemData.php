<?php

namespace Pixelbinio\Pixelbin\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetItemData
{
    protected $logger;
    protected $helperData;
    protected $assetRepo;
    
    /**
     * AfterGetImageData constructor.
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param AbstractItem $item
     * @param $result
     * @return mixed
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($result['product_id'] > 0) {
                $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $result);

                $imagePathArray = explode('media', $imagePath);

                $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];
                
                if (@getimagesize($pixelbinImage)) {
                    $image = $pixelbinImage;
                } else {
                    $image = $this->helperData->getDefaultImage();
                }

                $result['product_image']['src'] = $image;
            }
        } catch (\Exception $e) {
        }

        return $result;
    }

    // public function aroundGetItemData(AbstractItem $subject, $proceed, $item)
    // {

    //     $result = $proceed($item);

    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $product = $objectManager->create('Magento\Catalog\Model\Product')->load($result['product_id']);

    //     /* thum url */ 
    //     $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface'); 
    //     $currentStore = $storeManager->getStore();
    //     $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);


    //     if($product->getThumbnail()){

    //         $image = $mediaUrl.$product->getThumbnail();
    //         $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $result);

    //         $imagePathArray = explode('media', $imagePath);

    //         $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];

    //         if (@getimagesize($pixelbinImage)) {
    //             $image = $pixelbinImage;
    //         } else {
    //             $image = $this->helperData->getDefaultImage();
    //         }
    //         $result['product_image']['src'] = $image;
    //     }
    //     else{
    //         $result['product_image']['src'] = $this->helperData->getDefaultImage();
    //     }
    //     return $result;
    // }

}