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
 * @version     1.0.0
 */

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
