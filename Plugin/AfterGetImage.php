<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\AbstractProduct;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetImage
{
    protected $logger;
    protected $helperData;
    protected $assetRepo;

    /**
     * AfterGetImage constructor.
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
     * @param AbstractProduct $subject
     * @param $result
     * @param $product
     * @param $imageId
     * @param $attributes
     * @return mixed
     */
    public function afterGetImage(AbstractProduct $subject, $result, $product, $imageId, $attributes) 
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }
        
        try {
            if ($product) {
                    $image = [];
                    $imageUrl = $product->getProductUrl();
                    $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);

                    $imagePathArray = explode('media', $imagePath);

                    $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];
                    
                    if (@getimagesize($pixelbinImage)) {
                        $image['image_url'] = $pixelbinImage;
                    } else {
                        //$image['image_url'] = $this->assetRepo->getUrl('Pixelbinio_Pixelbin::images/no-image-placeholder.png');
                        $image['image_url'] = $this->helperData->getDefaultImage();
                    }
                    $image['width'] = "240";
                    $image['height'] = "300";
                    $image['label'] = $product->getName();
                    $image['ratio'] = "1.25";
                    $image['custom_attributes'] = "";
                    $image['resized_image_width'] = "399";
                    $image['resized_image_height'] = "399";
                    $image['product_id'] = $product->getId();
                if ($image) {
                    $result->setData($image);
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }
}