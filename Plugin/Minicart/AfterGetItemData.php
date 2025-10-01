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

namespace Pixelbinio\Pixelbin\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetItemData
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
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * AfterGetImageData constructor.
     *
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
     * After get item data
     *
     * @param AbstractItem $item
     * @param array $result
     * @return array
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }
        try {
            if ($result['product_id'] > 0) {
                $image = $this->helperData->replaceProductImageUrlWithPixelbin($result['product_image']['src']);
                $result['product_image']['src'] = $image;
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL Minicart error - " . $e->getMessage());
        }
        return $result;
    }
}
