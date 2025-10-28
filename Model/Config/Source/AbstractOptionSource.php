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

namespace Pixelbinio\Pixelbin\Model\Config\Source;

class AbstractOptionSource
{
    /**
     * Retrieve options array.
     *
     * @param array $options
     * @return array
     */
    public function processOptionArray($options): array
    {
        $result = [];
        foreach ($options as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }
}
