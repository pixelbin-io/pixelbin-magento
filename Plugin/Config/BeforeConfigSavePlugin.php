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

namespace Pixelbinio\Pixelbin\Plugin\Config;

use Magento\Config\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Api\Data\PixelbinImageSyncLogsInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Model\PixelbinSynchronisation;

class BeforeConfigSavePlugin
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PixelbinSynchronisation
     */
    protected $pixelbinSynchronisation;

    /**
     * BeforeConfigSavePlugin construct
     *
     * @param HelperData $helperData
     * @param PixelbinSynchronisation $pixelbinSynchronisation
     */
    public function __construct(
        HelperData $helperData,
        PixelbinSynchronisation $pixelbinSynchronisation
    ) {
        $this->helperData = $helperData;
        $this->pixelbinSynchronisation = $pixelbinSynchronisation;
    }

    /**
     * Plugin before save config
     *
     * @param Config $subject
     * @throws LocalizedException
     */
    public function beforeSave(Config $subject)
    {
        $section = $subject->getSection();
        if ($section === 'pixelbin') {
            $configData = $subject->getData('groups');
            $globalCustomTransformation = $configData["image_transformations"]["fields"]
            ["global_custom_transformation"]["value"];

            $productCustomTransformation = $configData["image_transformations"]["fields"]
            ["product_custom_transformation"]["value"];

            if (
                !empty($globalCustomTransformation)
                && !preg_match(HelperData::TRANSFORMATION_REGEX, $globalCustomTransformation)
            ) {
                throw new LocalizedException(
                    __("Global transformation is not valid.")
                );
            }

            if (
                !empty($productCustomTransformation)
                && !preg_match(HelperData::TRANSFORMATION_REGEX, $productCustomTransformation)
            ) {
                throw new LocalizedException(
                    __("Product transformation is not valid.")
                );
            }

            if (isset($configData['app_configuration']['fields']['cloud_name']['value'])) {
                $newCloudName = $configData['app_configuration']['fields']['cloud_name']['value'];
                $oldCloudName = $subject->getConfigDataValue('pixelbin/app_configuration/cloud_name');
                if ($newCloudName != $oldCloudName) {
                    $pixelbinSynchronisationTableName = PixelbinSynchronisationInterface::TABLE_NAME;
                    $this->pixelbinSynchronisation->updateAllSyncToPending($pixelbinSynchronisationTableName);

                    $pixelbinImageSyncLogsTableName = PixelbinImageSyncLogsInterface::TABLE_NAME;
                    $this->pixelbinSynchronisation->truncateTable($pixelbinImageSyncLogsTableName);
                }
            }
        }
    }
}
