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
namespace Pixelbinio\Pixelbin\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Block\Product\ImageFactory as CatalogImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class ImageFactory
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
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
     * @param CatalogImageFactory $catalogImageFactory
     * @param callable $proceed
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageBlock
     * @throws NoSuchEntityException
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

        if ($this->helperData->isEnabledLazyload()) {
            $useOldImageTheme = is_string($imageBlock->getCustomAttributes()) ? 'old_' : '';
            $imageBlock->setTemplate(
                \preg_match('/\/image_with_borders.phtml$/', $imageBlock->getTemplate()) ?
                    'Pixelbinio_Pixelbin::product/' . $useOldImageTheme . 'image_with_borders.phtml' :
                    'Pixelbinio_Pixelbin::' . $useOldImageTheme . 'product/image.phtml'
            );
            $imageBlock->setLazyloadPlaceholder(HelperData::LAZYLOAD_DATA_PLACEHOLDER);
        }

        //Skip on Magento versions prior to 2.3
        if (is_array($product)) {
            return $imageBlock;
        }

        $mediaUrl = $this->helperData->getMediaUrl();

        try {
            if (strpos($imageBlock->getImageUrl(), $mediaUrl . 'catalog/product') === 0) {

                $generatedImageUrl = $this->helperData->replaceProductImageUrlWithPixelbin($imageBlock->getImageUrl());

                $imageBlock->setOriginalImageUrl($generatedImageUrl);
                $imageBlock->setImageUrl($generatedImageUrl);

                //Lazyload
                if ($this->helperData->isEnabledLazyload()) {
                    $imageBlock->setLazyloadPlaceholder($generatedImageUrl);
                }
            }
        } catch (\Exception $e) {
            $imageBlock = $proceed($product, $imageId, $attributes);
        }
        return $imageBlock;
    }
}
