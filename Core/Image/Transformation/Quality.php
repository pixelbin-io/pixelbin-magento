<?php

namespace Pixelbinio\Pixelbin\Core\Image\Transformation;

class Quality
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
     * Quality Value
     *
     * @param string $value
     * @return Quality
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new Quality($value);
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
