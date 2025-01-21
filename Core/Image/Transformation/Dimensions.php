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
 * @version     1.0.0
 */

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
     * @param $width
     * @param $height
     */
    private function __construct($width, $height)
    {
        $this->width = is_null($width) ? null : (int) round($width);
        $this->height = is_null($height) ? null : (int) round($height);
    }

    /**
     * get width value
     *
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * get height value
     *
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * get square dimensions
     *
     * @param $length
     * @return Dimensions
     */
    public static function square($length)
    {
        return new Dimensions($length, $length);
    }

    /**
     * get square missing dimensions
     *
     * @param Dimensions $dimensions
     * @return Dimensions
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
     * from width and height
     *
     * @param $width
     * @param $height
     * @return Dimensions
     */
    public static function fromWidthAndHeight($width, $height)
    {
        return new Dimensions($width, $height);
    }

    /**
     * get null for Dimensions
     *
     * @return Dimensions
     */
    public static function null()
    {
        return new Dimensions(null, null);
    }
}
