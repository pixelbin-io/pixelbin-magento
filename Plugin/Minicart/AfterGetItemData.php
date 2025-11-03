<?php

namespace Pixelbinio\Pixelbin\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Plugin\AbstractPixelbinPlugin;

class AfterGetItemData extends AbstractPixelbinPlugin
{
    /**
     * After get item data
     *
     * @param AbstractItem $item
     * @param array $result
     * @return array
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }
        try {
            if ($result['product_id'] > 0) {
                $image = $this->helperData->replaceProductImageUrlWithPixelbin($result['product_image']['src']);
                $result['product_image']['src'] = $image;
            }
        } catch (\Exception $e) {
            $this->helperData->logData("Image URL Minicart error - " . $e->getMessage());
        }
        return $result;
    }
}
