<?php

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AddImagesToGalleryBlock
{
    /**
     * @var CollectionFactory
     */
    protected $dataCollectionFactory;

    protected $logger;
    protected $helperData;

    /**
     * AddImagesToGalleryBlock constructor.
     *
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(
        CollectionFactory $dataCollectionFactory,
        Logger $logger,
        HelperData $helperData
    ) {
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * afterGalleryImages Plugin to change images and use external images stored in custom attribute
     *
     * @param Gallery $subject
     * @param Collection|null $images
     * @return Collection|null
     */
    public function afterGetGalleryImages(Gallery $subject, $images) {
        try {
            $hasExternalImage = false;
            // logic to get your external images url
            if (!$hasExternalImage) {
                return $images;
            }
            $product = $subject->getProduct();
            $images = $this->dataCollectionFactory->create();
            $productName = $product->getName();

            foreach($images as $image){
                $this->logger->info("Image URL Gallery => ", $image->getUrl());
            }

            $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $url);

            $imagePathArray = explode('media', $imagePath);

            $pixelbinImage = 'https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original'.$imagePathArray[1];

            $externalImages = ["https://cdn.pixelbinz0.de/v2/mute-sun-33a96d/original/__playground/playground-default.jpeg"]; // Array of images
            foreach ($externalImages as $item) {
                $imageId    = uniqid();
                $small      = $item;
                $medium     = $item;
                $large      = $item;
                $image = [
                    'file' => $large,
                    'media_type' => 'image',
                    'value_id' => $imageId, // unique value
                    'row_id' => $imageId, // unique value
                    'label' => $productName,
                    'label_default' => $productName,
                    'position' => 100,
                    'position_default' => 100,
                    'disabled' => 0,
                    'url'  => $large,
                    'path' => '',
                    'small_image_url' => $small,
                    'medium_image_url' => $medium,
                    'large_image_url' => $large
                ];
                $images->addItem(new DataObject($image));
            }

            return $images;
        } catch (\Exception $e) {
            return $images;
        }

    }
}