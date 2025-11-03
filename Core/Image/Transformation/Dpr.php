<?php

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
