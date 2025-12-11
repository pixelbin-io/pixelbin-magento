<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Block;

use Magento\Framework\View\Element\Template;
use Pixelbinio\Pixelbin\Helper\Data as Helper;

/**
 * Video Settings Block
 *
 * @api
 * @since 1.0.0
 */
class VideoSettings extends Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Template\Context $context
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get video setting
     *
     * @return array
     */
    public function getVideoSettings()
    {
        // Configuration values - these should ideally come from system config or parameters
        $autoplay = 'always';
        $controls = null;
        $isLoop = false;
        $streamMode = null;
        $streamModeFormat = null;
        $streamModeQuality = null;
        $progressiveSourceTypes = null;

        $transformation = [];
        $sourceTypes = null;

        // Build player settings
        $playerSettings = [
            'cloudName' => $this->helper->getAppCloudName(),
            'controls' => ($controls === 'all'),
            'autoplay' => $autoplay,
            'loop' => $isLoop,
            'chapters' => false,
            'muted' => false
        ];

        // Handle autoplay settings
        if ($autoplay) {
            $playerSettings['autoplayMode'] = $autoplay;
            if ($autoplay !== 'never') {
                $playerSettings['muted'] = true;
            }
        }

        // Handle stream mode optimization
        if ($streamMode === 'optimization') {
            if ($streamModeFormat === 'none' && $progressiveSourceTypes) {
                $sourceTypes = explode(',', (string)$progressiveSourceTypes);
            }
            if ($streamModeQuality) {
                $transformation[] = $streamModeQuality;
            }
        }

        // Handle ABR stream mode
        if ($streamMode === 'abr') {
            $sourceTypes = null;
        }

        // Build final settings array
        $settings = [
            'player_type' => 'default',
            'settings' => $playerSettings
        ];

        // Add transformation if present
        if (!empty($transformation)) {
            $settings['transformation'] = implode(',', $transformation);
        }

        // Add source types if present
        if ($sourceTypes) {
            $settings['source'] = ['sourceTypes' => $sourceTypes];
        }

        return $settings;
    }
}
