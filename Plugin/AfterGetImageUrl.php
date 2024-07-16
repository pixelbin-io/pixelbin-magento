<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\Image;

class AfterGetImageUrl
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
     * @param Image $image
     * @param $method
     * @return array|null
     */
    public function after__call(Image $image, $result, $method)
    {
        try {
            if ($method == 'getImageUrl' && $image->getProductId() > 0) {
                $pixelbinImage = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg';
                if (@getimagesize($pixelbinImage)) {
                    $result = $pixelbinImage;
                } else {
                    $result = $this->assetRepo->getUrl('Pixelbinio_Pixelbin::images/no-image-placeholder.png');
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

}