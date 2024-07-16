<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\Image;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AfterGetImageUrl
{
    protected $logger;
    protected $helperData;
    protected $assetRepo;

    /**
     * AfterGetImage constructor.
     */
    public function __construct(
        Logger $logger,
        HelperData $helperData,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param Image $image
     * @param $method
     * @return array|null
     */
    public function after__call(Image $image, $result, $method)
    {
        try {
            if ($method == 'getImageUrl' && $image->getProductId() > 0) {

                $imagePath = preg_replace('/\/catalog\/product\/cache\/[a-f0-9]{32}\//', '/', $result);

                $this->logger->info('Image URL- '.$imagePath);

                $pixelbinImage = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/catalog/product/w/t/wt09-yellow_main_1.jpg.jpg';
                if (@getimagesize($pixelbinImage)) {
                    $result = $pixelbinImage;
                } else {
                    $result = $this->assetRepo->getUrl('Pixelbinio_Pixelbin::images/no-image-placeholder.png');
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

}