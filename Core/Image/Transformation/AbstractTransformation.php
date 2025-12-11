<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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