<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Core\Image;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Core\Image;

class ImageFactory
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * ImageFactory constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Returns Image path
     *
     * @param string $imagePath
     * @param callable $localPathGenerator
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build($imagePath, callable $localPathGenerator)
    {
        if ($this->helperData->isModuleEnabled()) {
            return $imagePath;
        } else {
            //return $this->helperData->getDefaultImage();
            return \Pixelbinio\Pixelbin\Helper\Data::PIXELBIN_DEFAULT_IMAGE_URL;
        }
    }
}
