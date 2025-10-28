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

namespace Pixelbinio\Pixelbin\Model\Config\Source\Lazyload;

use Magento\Framework\Data\OptionSourceInterface;

class Effect implements OptionSourceInterface
{
    /**
     * Get all options
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'show',
                'label' => 'Show',
            ],
            [
                'value' => 'fadeIn',
                'label' => 'Fade In',
            ],
        ];
    }
}
