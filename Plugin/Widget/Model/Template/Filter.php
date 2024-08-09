<?php

namespace Pixelbinio\Pixelbin\Plugin\Widget\Model\Template;

use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Core\Image\ImageFactory;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Model\Template\Filter as CloudinaryWidgetFilter;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for Template Filter Model
 */
class Filter
{
    /**
     * @var ImageFactory
     */
    protected $_imageFactory;

    /**
     * @var UrlGenerator
     */
    protected $_urlGenerator;

    /**
     * @var ConfigurationInterface
     */
    protected $helperData;

    /**
     * @var CloudinaryWidgetFilter
     */
    protected $_cloudinaryWidgetFilter;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @method __construct
     * @param  StoreManagerInterface  $storeManager
     * @param  ImageFactory           $imageFactory
     * @param  HelperData           $helperData
     * @param  ConfigurationInterface $configuration
     * @param  CloudinaryWidgetFilter $cloudinaryWidgetFilter
     * @param  Registry               $coreRegistry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ImageFactory $imageFactory,
        HelperData $helperData,
        CloudinaryWidgetFilter $cloudinaryWidgetFilter,
        Registry $coreRegistry
    ) {
        $this->_imageFactory = $imageFactory;
        $this->helperData = $helperData;
        $this->_cloudinaryWidgetFilter = $cloudinaryWidgetFilter;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Around retrieve media file URL directive
     *
     * @param  \Magento\Widget\Model\Template\Filter $widgetFilter
     * @param  callable                              $proceed
     * @param  string[]                              $construction
     * @return string
     */
    public function aroundMediaDirective(\Magento\Widget\Model\Template\Filter $widgetFilter, callable $proceed, $construction)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $proceed($construction);
        }


        $params = $this->_cloudinaryWidgetFilter->getParams($construction[2]);
        if (!isset($params['url'])) {


            return $proceed($construction);
        }

        $url = (preg_match('/^&quot;.+&quot;$/', $params['url'])) ? preg_replace('/(^&quot;)|(&quot;$)/', '', $params['url']) : $params['url'];

        $image = $this->_imageFactory->build(
            $url,
            function () use ($proceed, $construction) {

                return $proceed($construction);
            }
        );

        $generated = $this->helperData->getAppZone().$image;
        if (@getimagesize($generated)) {
            return $generated;
        } else {
            return $this->helperData->getDefaultImage();
        }

        return $generated;
    }
}
