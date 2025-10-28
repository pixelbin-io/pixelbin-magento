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
