<?php
/**
 * Pixelbinio
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Pixelbinio
 * @package     Pixelbinio_Pixelbin
 */
declare(strict_types=1);

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
