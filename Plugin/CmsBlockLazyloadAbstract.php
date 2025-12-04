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

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Cms\Block\Widget\Block as CmsBlockWidget;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Framework\Registry;

class CmsBlockLazyloadAbstract
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
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @method __construct
     * @param Logger $logger
     * @param HelperData $helperData
     * @param Registry $coreRegistry
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData,
        Registry $coreRegistry
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Process image
     *
     * @param CmsBlockWidget $subject
     * @param string $html
     * @return false|mixed|string
     */
    protected function process($subject, $html)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $html;
        }

        if (stripos($html, "<img ") !== false) {
            $dom = new \domDocument();
            $useErrors = libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            libxml_use_internal_errors($useErrors);
            $dom->preserveWhiteSpace = false;
            $modified = 0;

            foreach ($dom->getElementsByTagName('img') as $element) {
                if (
                    strpos($element->getAttribute('class'), "lazyload") === false &&
                    strpos($element->getAttribute('class'), "owl-lazy") === false &&
                    ($image = $element->getAttribute('src')) !== null
                ) {
                    $placeholderUrl = $this->helperData->replaceCmsImageUrlWithPixelbin($image);
                    $modified++;

                    if (
                        $this->helperData->isEnabledLazyload() &&
                        $this->helperData->isLazyloadAutoReplaceCmsBlocks() &&
                        !in_array($subject->getBlockId(), $this->helperData->getLazyloadIgnoredCmsBlocksArray())
                    ) {
                        $element->setAttribute('class', 'pixelbin-lazyload ' . $element->getAttribute('class'));
                        $element->setAttribute('data-original', $placeholderUrl);
                    }
                    $element->setAttribute('src', $placeholderUrl);
                }
            }

            if ($modified) {
                $html = $dom->saveHTML();
            }
        }
        return $html;
    }
}
