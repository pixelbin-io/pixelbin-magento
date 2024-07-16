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
        if (!$this->helperData->isModuleEnabled()) {
            return $result;
        }

        try {
            if ($method == 'getImageUrl' && $image->getProductId() > 0) {

                $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $result);

                $imagePathArray = explode('media', $imagePath);

                $pixelbinImage = $this->helperData->getAppZone().$imagePathArray[1];
                
                if (@getimagesize($pixelbinImage)) {
                    $result = $pixelbinImage;
                } else {
                    $result = $this->helperData->getDefaultImage();
                }
            }
        } catch (\Exception $e) {
        }
        
        return $result;
    }

}