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
     * To string
     *
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
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new DefaultImage($value);
    }

    /**
     * Null value for Default Image
     *
     * @return DefaultImage
     * @codingStandardsIgnoreStart
     */
    public static function null()
    {
        return new DefaultImage(null);
    }
}
