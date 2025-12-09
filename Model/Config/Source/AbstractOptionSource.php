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

namespace Pixelbinio\Pixelbin\Model\Config\Source;

abstract class AbstractOptionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve option array
     * Child classes must implement this method
     *
     * @return string[]
     */
    abstract public function getOptionArray();

    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = $this->getOptionArray();
        return $this->processOptionArray($options);
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions(): array
    {
        $options = $this->getOptionArray();
        return $this->processOptionArray($options);
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string|null
     */
    public function getOptionText($optionId): ?string
    {
        $options = $this->getOptionArray();
        return $options[$optionId] ?? "";
    }

    /**
     * Process option array to convert to format expected by Magento
     * Override this method if custom processing is needed
     *
     * @param array $options
     * @return array
     */
    protected function processOptionArray(array $options): array
    {
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}
