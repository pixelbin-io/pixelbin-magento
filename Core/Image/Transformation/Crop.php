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

class Crop
{
    public const PAD = 'pad';
    public const LPAD = 'lpad';
    public const FIT = 'fit';
    public const LIMIT = 'limit';

    /**
     * @var value
     */
    private $value;

    /**
     * @param string $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * From String value for Crop
     *
     * @param string $value
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new Crop($value);
    }

    /**
     * Pad for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function pad()
    {
        return new Crop(self::PAD);
    }

    /**
     * Lpad for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function lpad()
    {
        return new Crop(self::LPAD);
    }

    /**
     * Fit for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function fit()
    {
        return new Crop(self::FIT);
    }

    /**
     * Limit for Crop
     *
     * @return Crop
     * @codingStandardsIgnoreStart
     */
    public static function limit()
    {
        return new Crop(self::LIMIT);
    }

    /**
     * To String
     *
     * @return value
     */
    public function __toString()
    {
        return $this->value;
    }
}
