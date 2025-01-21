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

class DefaultImage
{
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
     * @return mixed
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * From String value for Default Image
     *
     * @param string $value
     * @return DefaultImage
     */
    public static function fromString($value)
    {
        return new DefaultImage($value);
    }

    /**
     * Null value for Default Image
     *
     * @return DefaultImage
     */
    public static function null()
    {
        return new DefaultImage(null);
    }
}
