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
 * @version     1.0.1
 */

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\Image;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetImageUrl extends AbstractPixelbinPlugin
{
    /**
     * After Plugin to change Image Url on Call method for PDP page
     *
     * @param Image $image
     * @param array $result
     * @param string $method
     * @return array|null
     * //@codingStandardsIgnoreStart
     */
    public function after__call(Image $image, $result, $method)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($method == 'getImageUrl' && $image->getProductId() > 0) {
                $result = $this->helperData->replaceProductImageUrlWithPixelbin($result);
            }
        } catch (\Exception $e) {
            $this->helperData->logData("Image URL PDP error" . $e->getMessage());
        }

        return $result;
    }
}
