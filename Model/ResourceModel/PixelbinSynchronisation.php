<?php

namespace Pixelbinio\Pixelbin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;

class PixelbinSynchronisation extends AbstractDb
{
    /**
     * Construct method
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            PixelbinSynchronisationInterface::TABLE_NAME,
            PixelbinSynchronisationInterface::KEY_ENTITY_ID
        );
    }
}
