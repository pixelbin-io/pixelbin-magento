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

use Magento\Catalog\Block\Product\Image;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetImageUrl
{
    protected $assetRepo;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @param Logger $logger
     * @param HelperData $helperData
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->assetRepo = $assetRepo;
    }

    /**
     * After Plugin to change Image Url on Call method for PDP page
     *
     * @param Image $image
     * @param $method
     * @return array|null
     */
    public function after__call(Image $image, $result, $method)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($method == 'getImageUrl' && $image->getProductId() > 0) {
                $result = $this->helperData->replaceProductImageUrlWithPixelbin($result);
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL PDP error" . $e->getMessage());
        }

        return $result;
    }
}
