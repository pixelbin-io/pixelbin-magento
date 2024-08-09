<?php

namespace Pixelbinio\Pixelbin\Core\Image;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Core\Image;

class ImageFactory
{
    /**
     * @var ConfigurationInterface
     */
    private $helperData;

  

    /**
     * ImageFactory constructor.
     *
     * @param ConfigurationInterface $helperData
     * @param SynchronizationCheck   $synchronizationChecker
     */
    public function __construct(
        HelperData $helperData, 
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * @param  $imagePath
     * @return Image
     */
    public function build($imagePath, callable $localPathGenerator)
    {
        //$migratedPath = $this->helperData->getMigratedPath($imagePath);

        if ($this->helperData->isModuleEnabled()) {
            return $imagePath;
        } else {
            return $this->helperData->getDefaultImage();
        }
    }
}
