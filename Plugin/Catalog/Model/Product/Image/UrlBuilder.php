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
namespace Pixelbinio\Pixelbin\Plugin\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Catalog\Model\Product\Image\UrlBuilder as CatalogUrlBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\ConfigInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class UrlBuilder
{
    /**
     * @var ConfigInterface
     */
    protected $presentationConfig;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param ConfigInterface $presentationConfig
     * @param HelperData $helperData
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        HelperData $helperData
    ) {
        $this->presentationConfig = $presentationConfig;
        $this->helperData = $helperData;
    }

    /**
     * Build image url using base path and params
     *
     * @param CatalogUrlBuilder $catalogUrlBuilder
     * @param callable $proceed
     * @param string $baseFilePath
     * @param string $imageDisplayArea
     * @return string
     * @throws NoSuchEntityException
     */
    public function aroundGetUrl(
        CatalogUrlBuilder $catalogUrlBuilder,
        callable $proceed,
        string $baseFilePath,
        string $imageDisplayArea
    ) {
        $url = $proceed($baseFilePath, $imageDisplayArea);

        if (!$this->helperData->isModuleEnabled()) {
            return $url;
        }

        if ($url === 'no_selection') {
            return $url;
        }

        $mediaUrl = $this->helperData->getMediaUrl();

        try {
            if (strpos($url, $mediaUrl . 'catalog/product') === 0) {
                $imageArguments = $this->presentationConfig->getViewConfig()->getMediaAttributes(
                    'Magento_Catalog',
                    CatalogImageHelper::MEDIA_TYPE_CONFIG_NODE,
                    $imageDisplayArea
                );

                $url = $this->helperData->replaceProductImageUrlWithPixelbin($url);
            }
        } catch (\Exception $e) {
            $url = $proceed($baseFilePath, $imageDisplayArea);
        }
        return $url;
    }
}
