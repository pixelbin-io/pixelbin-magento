<?php

namespace Pixelbinio\Pixelbin\Model\Config\Source\Lazyload;

use Magento\Framework\Data\OptionSourceInterface;

class Placeholder implements OptionSourceInterface
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
                'value' => 'blur',
                'label' => 'Blur',
            ],
            // [
            //     'value' => 'pixelate',
            //     'label' => 'Pixelate',
            // ],
            // [
            //     'value' => 'predominant-color',
            //     'label' => 'Predominant color',
            // ],
            // [
            //     'value' => 'vectorize',
            //     'label' => 'Vectorize',
            // ],
        ];
    }
}
