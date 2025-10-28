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
 * @version     1.0.1
 */

namespace Pixelbinio\Pixelbin\Core\Image\Transformation;

class Dpr
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
     * From String value
     *
     * @param string $value
     * @return Dpr
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new Dpr($value);
    }

    /**
     * To String value
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->value;
    }
}
