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

namespace Pixelbinio\Pixelbin\Plugin\Catalog\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Block\Product\ImageFactory as CatalogImageFactory;
use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class ImageFactory
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
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $presentationConfig
     * @param Logger $logger
     * @param HelperData $helperData
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        Logger $logger,
        HelperData $helperData,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->assetRepo = $assetRepo;
        $this->dimensions = null;
        $this->imageFile = null;
        $this->keepFrame = true;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getStringCustomAttributes(array $attributes)
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            if ($name != 'class') {
                $result[] = $name . '="' . $value . '"';
            }
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Create image block from product
     *
     * @param  CatalogImageFactory $catalogImageFactory
     * @param  callable            $proceed
     * @param  Product             $product
     * @param  string              $imageId
     * @param  array|null          $attributes
     * @return ImageBlock
     */
    public function aroundCreate(
        CatalogImageFactory $catalogImageFactory,
        callable $proceed,
        $product = null,
        $imageId = null,
        $attributes = null
    ) {
        //@codingStandardsIgnoreStart
        $imageBlock = call_user_func_array($proceed, array_slice(func_get_args(), 2));
        //@codingStandardsIgnoreEnd

        if (!$this->helperData->isModuleEnabled()) {
            return $imageBlock;
        }

        if ($imageBlock->getImageUrl() === 'no_selection') {
            return $imageBlock;
        }

        //Skip on Magento versions prior to 2.3
        if (is_array($product)) {
            return $imageBlock;
        }

        $mediaUrl = $this->helperData->getMediaUrl();

        try {
            if (strpos($imageBlock->getImageUrl(), $mediaUrl . 'catalog/product') === 0) {
                $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
                    'Magento_Catalog',
                    CatalogImageHelper::MEDIA_TYPE_CONFIG_NODE,
                    $imageId
                );

                $generatedImageUrl = $this->helperData->replaceProductImageUrlWithPixelbin($imageBlock->getImageUrl());

                $imageBlock->setOriginalImageUrl($imageBlock->setImageUrl());
                $imageBlock->setImageUrl($generatedImageUrl);

                //Lazyload
            }
        } catch (\Exception $e) {
            $imageBlock = $proceed($product, $imageId, $attributes);
        }

        return $imageBlock;
    }
}
