<?php

namespace Pixelbinio\Pixelbin\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;

class AfterGetItemData
{
    /**
     * AfterGetImageData constructor.
     */
    public function __construct(
    )
    {
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
                $image = "https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg";
                $result['product_image']['src'] = $image;
            }
        } catch (\Exception $e) {
        }

        return $result;
    }

}