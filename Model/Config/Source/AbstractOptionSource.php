<?php

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
