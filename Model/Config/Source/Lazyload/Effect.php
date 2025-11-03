<?php

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
