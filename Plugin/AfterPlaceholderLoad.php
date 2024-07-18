<?php

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
     * @param \Magento\Catalog\Model\View\Asset\Placeholder $subject
     * @param $result
     * @return string
     */
    public function afterGetUrl(
        \Magento\Catalog\Model\View\Asset\Placeholder $subject, $result
    ) {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($result) {
                return $this->helperData->getDefaultImage();
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL error - " . $e->getMessage());
        }

        return $result;
    }
}