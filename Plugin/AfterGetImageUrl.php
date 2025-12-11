<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
