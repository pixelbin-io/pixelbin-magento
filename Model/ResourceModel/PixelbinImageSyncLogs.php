<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pixelbinio\Pixelbin\Api\Data\PixelbinImageSyncLogsInterface;

class PixelbinImageSyncLogs extends AbstractDb
{
    /**
     * Construct method
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            PixelbinImageSyncLogsInterface::TABLE_NAME,
            PixelbinImageSyncLogsInterface::KEY_ENTITY_ID
        );
    }
}
