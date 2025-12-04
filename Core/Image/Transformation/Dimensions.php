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

class Dimensions
{
    /**
     * @var int|null
     */
    private $width;

    /**
     * @var int|null
     */
    private $height;

    /**
     * @param int $width
     * @param int $height
     */
    private function __construct($width, $height)
    {
        if ($width === null) {
            $this->width = null;
        } else {
            $this->width = (int) round($width);
        }

        if ($width === null) {
            $this->height = null;
        } else {
            $this->height = (int) round($height);
        }
    }

    /**
     * Get width value
     *
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get height value
     *
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get square dimensions
     *
     * @param int $length
     * @return Dimensions
     * //@codingStandardsIgnoreStart
     */
    public static function square($length)
    {
        return new Dimensions($length, $length);
    }

    /**
     * Get square missing dimensions
     *
     * @param Dimensions $dimensions
     * @return Dimensions
     * //@codingStandardsIgnoreStart
     */
    public static function squareMissingDimension(Dimensions $dimensions)
    {
        if (!$dimensions->getWidth()) {
            return Dimensions::square($dimensions->getHeight());
        } elseif (!$dimensions->getHeight()) {
            return Dimensions::square($dimensions->getWidth());
        }

        return $dimensions;
    }

    /**
     * From width and height
     *
     * @param int|float $width
     * @param int|float $height
     * @return Dimensions
     * //@codingStandardsIgnoreStart
     */
    public static function fromWidthAndHeight($width, $height)
    {
        return new Dimensions($width, $height);
    }

    /**
     * Get null for dimensions
     *
     * @return Dimensions
     * //@codingStandardsIgnoreStart
     */
    public static function null()
    {
        return new Dimensions(null, null);
    }
}
