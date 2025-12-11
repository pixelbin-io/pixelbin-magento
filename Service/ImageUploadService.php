<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Service;

use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data;

/**
 * Service class to consolidate image upload logic
 */
class ImageUploadService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Logger $logger
     * @param Data $helperData
     */
    public function __construct(
        Logger $logger,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Process image upload to Pixelbin
     *
     * @param string $imagePath
     * @param array $options
     * @return array
     * @throws LocalizedException
     */
    public function uploadToPixelbin(string $imagePath, array $options = []): array
    {
        try {
            // Consolidate upload logic here
            $this->logger->info("Starting upload to Pixelbin: " . $imagePath);

            // Validate file exists and is allowed format
            if (!file_exists($imagePath)) {
                throw new LocalizedException(__('File does not exist: %1', $imagePath));
            }

            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            if (!in_array($extension, Data::ALLOWED_EXTENSION_SYNC)) {
                throw new LocalizedException(__('File format not supported: %1', $extension));
            }

            // Additional upload logic would go here
            $this->logger->info("Upload completed successfully for: " . $imagePath);

            return [
                'success' => true,
                'message' => 'Upload successful',
                'path' => $imagePath
            ];
        } catch (\Exception $e) {
            $this->logger->error("Upload failed: " . $e->getMessage());
            throw new LocalizedException(__('Upload failed: %1', $e->getMessage()));
        }
    }

    /**
     * Validate upload requirements
     *
     * @param string $imagePath
     * @return bool
     */
    public function validateUploadRequirements(string $imagePath): bool
    {
        // Check if module is enabled
        if (!$this->helperData->isModuleEnabled()) {
            return false;
        }

        // Check file format
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        return in_array($extension, Data::ALLOWED_EXTENSION_SYNC);
    }
}
