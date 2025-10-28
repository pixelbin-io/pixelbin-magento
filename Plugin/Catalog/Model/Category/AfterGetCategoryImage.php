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

namespace Pixelbinio\Pixelbin\Plugin\Catalog\Model\Category;

use Magento\Catalog\Model\Category\Image;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetCategoryImage
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
     * Build image url using base path and params
     *
     * @param Image $subject
     * @param array|string|string[] $result
     * @return string
     */
    public function afterGetUrl(
        Image $subject,
        $result,
    ) {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }
        try {
            if ($result) {
                $result = $this->helperData->replaceProductImageUrlWithPixelbin($result);
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL PDP error" . $e->getMessage());
        }
        return $result;
    }
}
