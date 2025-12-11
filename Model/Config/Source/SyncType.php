<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\Config\Source;

class SyncStatus extends AbstractOptionSource
{
    public const STATUS_PENDING = "pending";
    public const STATUS_PENDING_TO_START = "pending_to_start";
    public const STATUS_SYNCED = "synced";
    public const STATUS_ERROR = "error";

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __("Pending"),
            self::STATUS_PENDING_TO_START => __("Pending To Start"),
            self::STATUS_SYNCED => __("Synced"),
            self::STATUS_ERROR => __("Error")
        ];
    }
}
