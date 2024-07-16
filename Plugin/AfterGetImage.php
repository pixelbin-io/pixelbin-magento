<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\AbstractProduct;

class AfterGetImage
{
    protected $assetRepo;

    /**
     * AfterGetImage constructor.
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
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
    public function afterGetImage(AbstractProduct $subject, $result, $product, $imageId, $attributes) {
        try {
            if ($product) {
                    $image = [];
                    $pixelbinImage = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg';
                    if (@getimagesize($pixelbinImage)) {
                        $image['image_url'] = $pixelbinImage;
                    } else {
                        $image['image_url'] = $this->assetRepo->getUrl('Pixelbinio_Pixelbin::images/no-image-placeholder.png');
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