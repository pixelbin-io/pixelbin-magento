<?php

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
