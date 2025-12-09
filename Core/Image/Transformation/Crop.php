<?php

/**
 * Pixelbinio
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Pixelbinio
 * @package     Pixelbinio_Pixelbin
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