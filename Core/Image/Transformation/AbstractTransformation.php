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

abstract class AbstractTransformation
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $value
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Create instance from string value
     *
     * @param string $value
     * @return static
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new static($value);
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}