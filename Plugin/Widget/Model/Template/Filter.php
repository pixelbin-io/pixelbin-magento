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

namespace Pixelbinio\Pixelbin\Plugin\Widget\Model\Template;

use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Core\Image\ImageFactory;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Model\Template\Filter as WidgetFilter;
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
    protected $imageFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var WidgetFilter
     */
    protected $widgetFilter;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    protected $request;

    /**
     * @method __construct
     * @param  StoreManagerInterface  $storeManager
     * @param  ImageFactory           $imageFactory
     * @param  HelperData             $helperData
     * @param  WidgetFilter           $widgetFilter
     * @param  Registry               $coreRegistry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ImageFactory $imageFactory,
        HelperData $helperData,
        WidgetFilter $widgetFilter,
        Registry $coreRegistry,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->imageFactory = $imageFactory;
        $this->helperData = $helperData;
        $this->widgetFilter = $widgetFilter;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
    }

    /**
     * Around retrieve media file URL directive
     *
     * @param  \Magento\Widget\Model\Template\Filter $widgetFilter
     * @param  callable                              $proceed
     * @param  string[]                              $construction
     * @return string
     */
    public function aroundMediaDirective(
        \Magento\Widget\Model\Template\Filter $widgetFilter,
        callable $proceed,
                                              $construction
    ) {
        if (!$this->helperData->isModuleEnabled()) {
            return $proceed($construction);
        }

        $params = $this->widgetFilter->getParams($construction[2]);
        if (!isset($params['url'])) {
            return $proceed($construction);
        }

        //@codingStandardsIgnoreStart
        $url = (preg_match('/^&quot;.+&quot;$/', $params['url'])) ? preg_replace('/(^&quot;)|(&quot;$)/', '', $params['url']) : $params['url'];
        //@codingStandardsIgnoreEnd

        $image = $this->imageFactory->build(
            $url,
            function () use ($proceed, $construction) {
                return $proceed($construction);
            }
        );

        $path = parse_url($image, PHP_URL_PATH);
        $lastPart = basename($path);
        $extension = explode('.', $lastPart);
        $extension = strtolower($extension[1]);

        $allowed_formats = ['png', 'jpeg', 'jpg', 'webp', 'tiff', 'avif', 'bmp', 'heic', 'heif'];

        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();

        $path = $moduleName.'_'.$controller.'_'.$action;

        $generated = $this->helperData->getAppZone().$image;
        $generatedTf = $this->helperData->getAppZone().$image;

        $storeId = $this->helperData->getStoreId();
        if ($this->helperData->isImageTransformationEnabled($storeId) && in_array($extension, $allowed_formats)) {
            $globalTransformation = $this->helperData->getGlobalCustomTransformation($storeId);
            if ($globalTransformation) {
                $transformation = '/'.$globalTransformation.'/';
                if ($path == 'catalog_product_view') {
                    $transformation = str_replace('(', '%28', $transformation);
                    $transformation = str_replace(')', '%29', $transformation);
                    $generatedTf = preg_replace('/\/original\//', "$transformation", $generated);
                } else {
                    $generated = preg_replace('/\/original\//', "$transformation", $generated);
                }
            }
        }

        if ($this->helperData->checkSyncStatus()) {
            if ($path == 'catalog_product_view') {
                return $generatedTf;
            }
            return $generated;
        }

        return $this->helperData->getMediaUrl().$image;
    }
}
