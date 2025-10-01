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

namespace Pixelbinio\Pixelbin\Model\Config\Source;

class SyncType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const TYPE_CLI = "cli";
    public const TYPE_CRON = "cron";
    public const TYPE_MANUAL = "manual";

    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::TYPE_CLI => __("CLI"),
            self::TYPE_CRON => __("Cron"),
            self::TYPE_MANUAL => __("Manual")
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions(): array
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
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
}
