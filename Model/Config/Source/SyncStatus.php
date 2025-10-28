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

class SyncStatus extends AbstractOptionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const STATUS_PENDING = "pending";
    public const STATUS_PENDING_TO_START = "pending_to_start";
    public const STATUS_SYNCED = "synced";
    public const STATUS_ERROR = "error";

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
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __("Pending"),
            self::STATUS_PENDING_TO_START => __("Pending To Start"),
            self::STATUS_SYNCED => __("Synced"),
            self::STATUS_ERROR => __("Error")
        ];
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
}
