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

class Gravity
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
     * From string
     *
     * @param string $value
     * @return Gravity
     */
    public function fromString($value): Gravity
    {
        return new Gravity($value);
    }

    /**
     * Check Null
     *
     * @return Gravity
     */
    public function null()
    {
        return new Gravity(null);
    }
}
