<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pixelbinio\Pixelbin\Model\PixelbinSynchronisation as PixelbinSynchronisationModel;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation as PixelbinSynchronisationResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PixelbinSynchronisationModel::class,
            PixelbinSynchronisationResourceModel::class
        );
    }
}
