<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Core\Image\Transformation;

class FetchFormat
{
    public const FETCH_FORMAT_AUTO = 'auto';

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
     * Auto fetch format value
     *
     * @return FetchFormat
     * @codingStandardsIgnoreStart
     */
    public static function auto()
    {
        return self::fromString(self::FETCH_FORMAT_AUTO);
    }

    /**
     * Fetch format for from string value
     *
     * @param string $value
     * @return FetchFormat
     * @codingStandardsIgnoreStart
     */
    public static function fromString($value)
    {
        return new FetchFormat($value);
    }

    /**
     * To String conversion
     *
     * @return value
     */
    public function __toString()
    {
        return $this->value;
    }
}
