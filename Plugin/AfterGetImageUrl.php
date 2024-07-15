<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\Image;

class AfterGetImageUrl
{
    /**
     * AfterGetImage constructor.
     */
    public function __construct(
    )
    {
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
                $result = "https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg";
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

}