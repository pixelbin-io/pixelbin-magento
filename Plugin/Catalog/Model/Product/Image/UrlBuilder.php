<?php

namespace Pixelbinio\Pixelbin\Plugin\Catalog\Model\Product\Image;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Catalog\Model\Product\Image\UrlBuilder as CatalogUrlBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;

class UrlBuilder
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Image\ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var CloudinaryImageFactory
     */
    private $cloudinaryImageFactory;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var Dimensions
     */
    private $dimensions;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var string
     */
    private $imageFile;

    /**
     * @var bool
     */
    private $keepFrame;

    /**
     * @var TransformationModel
     */
    private $transformationModel;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface        $presentationConfig
     * @param CloudinaryImageFactory $cloudinaryImageFactory
     * @param UrlGenerator           $urlGenerator
     * @param ConfigurationInterface $configuration
     * @param TransformationFactory  $transformationFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->dimensions = null;
        $this->imageFile = null;
        $this->keepFrame = true;
    }

    /**
     * Build image url using base path and params
     *
     * @param  CatalogUrlBuilder $catalogUrlBuilder
     * @param  callable          $proceed
     * @param  string            $baseFilePath
     * @param  string            $imageDisplayArea
     * @return string
     */
    public function aroundGetUrl(CatalogUrlBuilder $catalogUrlBuilder, callable $proceed, string $baseFilePath, string $imageDisplayArea)
    {
        $url = $proceed($baseFilePath, $imageDisplayArea);

        // if (!$this->configuration->isEnabled()) {
        //     return $url;
        // }

        if ($url === 'no_selection') {
            return $url;
        }

        if (class_exists('\Magento\Catalog\Model\Product\Image\ParamsBuilder')) {
            $this->imageParamsBuilder = $this->objectManager->get('\Magento\Catalog\Model\Product\Image\ParamsBuilder');
        } else {
            //Skip on Magento versions prior to 2.3
            return $url;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        try {
            if (strpos($url, $mediaUrl . 'catalog/product') === 0) {
                $imageArguments = $this->presentationConfig->getViewConfig()->getMediaAttributes(
                    'Magento_Catalog',
                    CatalogImageHelper::MEDIA_TYPE_CONFIG_NODE,
                    $imageDisplayArea
                );
                $imageMiscParams = $this->imageParamsBuilder->build($imageArguments);

                $imagePath = preg_replace('/^' . preg_quote($mediaUrl, '/') . '/', '/', $url);
                $imagePath = preg_replace('/\/catalog\/product\/cache\/[a-f0-9]{32}\//', '/', $imagePath);

                

                $generatedImageUrl = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg';

                $url = $generatedImageUrl;
            }
        } catch (\Exception $e) {
            $url = $proceed($baseFilePath, $imageDisplayArea);
        }

        return $url;
    }

}
