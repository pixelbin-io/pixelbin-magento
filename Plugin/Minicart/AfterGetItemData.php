<?php

namespace Pixelbinio\Pixelbin\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;

class AfterGetItemData
{
    protected $assetRepo;
    
    /**
     * AfterGetImageData constructor.
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param AbstractItem $item
     * @param $result
     * @return mixed
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        try {
            if ($result['product_id'] > 0) {
                $pixelbinImage = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg';
                if (@getimagesize($pixelbinImage)) {
                    $image = $pixelbinImage;
                } else {
                    $image = $this->assetRepo->getUrl('Pixelbinio_Pixelbin::images/no-image-placeholder.png');
                }
                $result['product_image']['src'] = $image;
            }
        } catch (\Exception $e) {
        }

        return $result;
    }

}