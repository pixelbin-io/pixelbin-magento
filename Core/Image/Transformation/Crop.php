<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Core\Image\Transformation;

// File: Crop.php
// This extends AbstractTransformation to eliminate code duplication

class Crop extends AbstractTransformation
{
    public const PAD = 'pad';
    public const LPAD = 'lpad';
    public const FIT = 'fit';
    public const LIMIT = 'limit';

    /**
     * Pad for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function pad()
    {
        return new self(self::PAD);
    }

    /**
     * Lpad for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function lpad()
    {
        return new self(self::LPAD);
    }

    /**
     * Fit for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function fit()
    {
        return new self(self::FIT);
    }

    /**
     * Limit for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function limit()
    {
        return new self(self::LIMIT);
    }
}