<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Controller\Adminhtml\AbstractUploadController;

/**
 * Product Gallery image upload controller for different types of image type
 */
class Upload extends AbstractUploadController implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Catalog::products';

    private const ALLOWED_MIME_TYPES = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'svg' => 'image/svg',
        'svg-xml' => 'image/svg+xml',
        'webp' => 'image/webp',
        'tiff' => 'image/tiff',
        'tif' => 'image/tif',
        'avif' => 'image/avif',
        'raw' => 'image/raw'
    ];

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Image\AdapterFactory|null $adapterFactory
     * @param \Magento\Framework\Filesystem|null $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config|null $productMediaConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        ?\Magento\Framework\Image\AdapterFactory $adapterFactory = null,
        ?\Magento\Framework\Filesystem $filesystem = null,
        ?\Magento\Catalog\Model\Product\Media\Config $productMediaConfig = null
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->adapterFactory = $this->getDependencyWithFallback(
            $adapterFactory,
            \Magento\Framework\Image\AdapterFactory::class
        );
        $this->filesystem = $this->getDependencyWithFallback(
            $filesystem,
            \Magento\Framework\Filesystem::class
        );
        $this->productMediaConfig = $this->getDependencyWithFallback(
            $productMediaConfig,
            \Magento\Catalog\Model\Product\Media\Config::class
        );
    }

    /**
     * Upload image(s) to the product gallery.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $result = $this->executeUploadWithErrorHandling(function () {
            return $this->performUpload();
        });

        return $this->createRawResponse($result);
    }

    /**
     * Perform the actual upload operation
     *
     * @return array
     * @throws \Exception
     */
    private function performUpload(): array
    {
        $uploader = $this->createUploader('image', $this->getAllowedExtensions());

        $imageAdapter = $this->adapterFactory->create();
        $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');

        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $result = $uploader->save(
            $mediaDirectory->getAbsolutePath($this->productMediaConfig->getBaseTmpMediaPath())
        );

        $this->_eventManager->dispatch(
            'catalog_product_gallery_upload_image_after',
            ['result' => $result, 'action' => $this]
        );

        return $this->formatUploadResult($result);
    }

    /**
     * Format upload result
     *
     * @param mixed $result
     * @return array
     */
    private function formatUploadResult($result): array
    {
        if (!is_array($result)) {
            return ['error' => 'Something went wrong while saving the file(s).'];
        }

        unset($result['tmp_name'], $result['path']);
        $result['url'] = $this->productMediaConfig->getTmpMediaUrl($result['file']);
        $result['file'] = $result['file'] . '.tmp';

        return $result;
    }

    /**
     * Create raw response with JSON content
     *
     * @param array $data
     * @return \Magento\Framework\Controller\Result\Raw
     */
    private function createRawResponse(array $data): \Magento\Framework\Controller\Result\Raw
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($data));
        return $response;
    }

    /**
     * Get the set of allowed file extensions.
     *
     * @return array
     */
    private function getAllowedExtensions(): array
    {
        return array_keys(self::ALLOWED_MIME_TYPES);
    }
}
