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

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\View\Asset\Repository;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Catalog\Model\Product;

class AfterGetImage
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
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @param Logger $logger
     * @param HelperData $helperData
     * @param Repository $assetRepo
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData,
        Repository $assetRepo
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->assetRepo = $assetRepo;
    }

    /**
     * After Plugin to change Image URL in PDP page
     *
     * @param AbstractProduct $subject
     * @param object $result
     * @param Product $product
     * @param int $imageId
     * @param array $attributes
     * @return object
     */
    public function afterGetImage(AbstractProduct $subject, $result, $product, $imageId, $attributes)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($product) {
                    $image = [];
                    $imageUrl = $product->getProductUrl();
                    $pixelbinImage = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);

                    // $imagePathArray = explode('media', $imagePath);
                    // $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];

                if (isset($pixelbinImage)) {
                    $image['image_url'] = $pixelbinImage;
                } else {
                    $image['image_url'] = $this->helperData->getDefaultImage();
                }

                    $image['width'] = "240";
                    $image['height'] = "300";
                    $image['label'] = $product->getName();
                    $image['ratio'] = "1.25";
                    $image['custom_attributes'] = "";
                    $image['resized_image_width'] = "399";
                    $image['resized_image_height'] = "399";
                    $image['product_id'] = $product->getId();
                if ($image) {
                    $result->setData($image);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info("Image URL PLP error" . $e->getMessage());
        }
        return $result;
    }
}
