<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\ContentType\Image;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Pixelbinio\Pixelbin\Controller\Adminhtml\AbstractUploadController;

/**
 * Image upload controller class for Content type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends AbstractUploadController implements HttpPostActionInterface
{
    public const UPLOAD_DIR = 'wysiwyg';
    public const ADMIN_RESOURCE = 'Magento_Backend::content';

    private const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif'];

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $cmsWysiwygImages;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @param Filesystem|null $filesystem
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        ?Filesystem $filesystem = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->cmsWysiwygImages = $cmsWysiwygImages;

        $filesystem = $this->getDependencyWithFallback($filesystem, Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Allow users to upload images to the folder structure
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->executeUploadWithErrorHandling(function () {
            return $this->performUpload();
        });

        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Perform the actual upload operation
     *
     * @return array
     * @throws \Exception
     */
    private function performUpload(): array
    {
        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        $this->configureUploader($fileUploader);

        $result = $fileUploader->save($this->getUploadDir());

        return $this->formatUploadResult($result);
    }

    /**
     * Configure file uploader settings
     *
     * @param \Magento\Framework\File\Uploader $uploader
     * @return void
     */
    private function configureUploader(\Magento\Framework\File\Uploader $uploader): void
    {
        $uploader->setFilesDispersion(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions(self::ALLOWED_EXTENSIONS);
        $uploader->setAllowCreateFolders(true);
    }

    /**
     * Format upload result with additional metadata
     *
     * @param array $result
     * @return array
     */
    private function formatUploadResult(array $result): array
    {
        $baseUrl = $this->_backendUrl->getBaseUrl([
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ]);

        $result['id'] = $this->cmsWysiwygImages->idEncode($result['file']);
        $result['url'] = $baseUrl . $this->buildFilePath(self::UPLOAD_DIR, $result['file']);

        return $result;
    }

    /**
     * Return the upload directory
     *
     * @return string
     */
    private function getUploadDir(): string
    {
        return $this->mediaDirectory->getAbsolutePath(self::UPLOAD_DIR);
    }
}
