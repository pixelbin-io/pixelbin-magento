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
