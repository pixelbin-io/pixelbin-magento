<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Framework\Registry;

/**
 * Class CmsBlockLazyloadAbstract
 */
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
                if (strpos($element->getAttribute('class'), "lazyload") === false && strpos($element->getAttribute('class'), "owl-lazy") === false && ($image = $element->getAttribute('src')) !== null) {
                    
                    $placeholderUrl = $this->helperData->replaceCmsImageUrlWithPixelbin($image);
                    $modified++;

                    if ($this->helperData->isLazyloadAutoReplaceCmsBlocks()) {
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
