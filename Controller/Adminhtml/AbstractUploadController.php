<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Abstract base class for upload controllers to reduce code duplication
 */
abstract class AbstractUploadController extends Action
{
    /**
     * Execute upload with error handling
     *
     * @param callable $uploadCallback
     * @return array
     */
    protected function executeUploadWithErrorHandling(callable $uploadCallback): array
    {
        try {
            $result = $uploadCallback();

            if (!is_array($result)) {
                return ['error' => 'Something went wrong while saving the file(s).'];
            }

            return $result;
        } catch (LocalizedException $e) {
            return ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'errorcode' => 0];
        }
    }

    /**
     * Get dependency with ObjectManager fallback
     *
     * @param mixed $dependency
     * @param string $className
     * @return mixed
     */
    protected function getDependencyWithFallback($dependency, string $className)
    {
        return $dependency ?: ObjectManager::getInstance()->get($className);
    }

    /**
     * Create uploader instance
     *
     * @param string $fileId
     * @param array $allowedExtensions
     * @param bool $allowRenameFiles
     * @param bool $filesDispersion
     * @return \Magento\MediaStorage\Model\File\Uploader|\Magento\Framework\File\Uploader
     */
    protected function createUploader(
        string $fileId,
        array $allowedExtensions,
        bool $allowRenameFiles = true,
        bool $filesDispersion = true
    ) {
        $uploader = $this->_objectManager->create(
            \Magento\MediaStorage\Model\File\Uploader::class,
            ['fileId' => $fileId]
        );

        $uploader->setAllowedExtensions($allowedExtensions);
        $uploader->setAllowRenameFiles($allowRenameFiles);
        $uploader->setFilesDispersion($filesDispersion);

        return $uploader;
    }

    /**
     * Build file path from components
     *
     * @param string $path
     * @param string $fileName
     * @return string
     */
    protected function buildFilePath(string $path, string $fileName): string
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }
}
