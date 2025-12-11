<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Plugin\File\Validator;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class NotProtectedExtensionPlugin
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * NotProtectedExtensionPlugin constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Remove vector images from protected extensions list
     *
     * @param NotProtectedExtension $subject
     * @param string $result
     * @return string|string[]
     */
    public function afterGetProtectedFileExtensions(NotProtectedExtension $subject, $result)
    {
        $vectorExtensions = $this->helperData->getVectorExtensions();

        if (is_string($result)) {
            $result = explode(',', $result);
        }

        foreach (array_keys($result) as $extension) {
            if (in_array($extension, $vectorExtensions)) {
                unset($result[$extension]);
            }
        }

        return $result;
    }
}
