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

use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterPlaceholderLoad
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
     * After get url
     *
     * @param \Magento\Catalog\Model\View\Asset\Placeholder $subject
     * @param string $result
     * @return string
     */
    public function afterGetUrl(
        \Magento\Catalog\Model\View\Asset\Placeholder $subject,
        $result
    ) {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($result) {
                if ($this->helperData->isDefaultImageEnabled()) {
                    return $this->helperData->getDefaultImage();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL error - " . $e->getMessage());
        }

        return $result;
    }
}
