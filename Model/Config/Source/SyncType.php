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

class SyncType extends AbstractOptionSource implements \Magento\Framework\Data\OptionSourceInterface
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
