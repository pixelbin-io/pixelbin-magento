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

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Model\View\Asset\Image;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AssetImagePlugin
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param Logger $logger
     * @param HelperData $helperData
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Change the custom URL
     *
     * @param Image $subject
     * @param string $result
     * @return string
     */
    public function afterGetUrl(Image $subject, $result)
    {
        $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $result);
        $imagePathArray = explode('media', $imagePath);
        $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];

        if (isset($pixelbinImage)) {
            $image_url = $pixelbinImage;
        } else {
            $image_url = $this->helperData->getDefaultImage();
        }

        return $image_url;
    }
}
