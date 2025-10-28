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

class Freeform
{
    /**
     * @var string
     */
    private $urlParameters;

    /**
     * Freeform constructor.
     *
     * @param string $urlParameters
     */
    public function __construct($urlParameters)
    {
        $this->urlParameters = $urlParameters;
    }

    /**
     * Free form from string value
     *
     * @param  string $value
     * @return Freeform
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new Freeform($value);
    }

    /**
     * To String conversion of URL
     *
     * @return string
     */
    public function __toString()
    {
        return $this->urlParameters;
    }
}
