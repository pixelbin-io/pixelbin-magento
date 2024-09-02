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

namespace Pixelbinio\Pixelbin\Block;

use Magento\Framework\View\Element\Template;
use Pixelbinio\Pixelbin\Helper\Data as Helper;

class VideoSettings extends template
{
    public function __construct(
        Template\Context $context,
        Helper $helper,
        array $data = []
    )
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getVideoSettings() {
        $settings = [];
        $sourceTypes = null;
        $videoFreeParams = false;

        $settings['player_type'] = 'pixelbin';
        if (!$videoFreeParams) {

            $transformation = [];

            $autoplay = 'never';
            $controls = null;
            $isLoop =  false;
            $playerSettings = [
                "cloudName" => $this->_helper->getAppCloudName(),
                'controls' => ($controls == 'all'),
                'autoplay' => ($autoplay != 'never'),
                'loop' => $isLoop,
                'chapters' => false
            ];

            $playerSettings['muted'] = false;

            if ($autoplay) {
                $playerSettings['autoplayMode'] = $autoplay;
                if ($autoplay != 'never') {
                    $playerSettings['muted'] = true;
                }
            }

            $streamMode = null;

            if ($streamMode == 'optimization') {
                $streamModeFormat = null;
                $streamModeQuality = null;
                $progressiveSourceTypes = null;

                if ($streamModeFormat == 'none' && $progressiveSourceTypes){
                    $sourceTypes = explode(',',(string)$progressiveSourceTypes);
                }
                if ($streamModeQuality){
                    $transformation[]=  $streamModeQuality;
                }
            }
            if ($streamMode == 'abr') {
                $sourceTypes = null;
            }

            $settings = [
                'player_type' => 'default',
                'settings' => $playerSettings
            ];

            if ($transformation && is_array($transformation)) {
                $settings['transformation'] = implode(',',$transformation);
            }

            if ($sourceTypes) {
                $settings['source'] = ['sourceTypes' => $sourceTypes];
            }
        }

        return $settings;
    }
}
