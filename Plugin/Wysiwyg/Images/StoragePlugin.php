<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Plugin\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class StoragePlugin
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * StoragePlugin constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Skip resizing vector images
     *
     * @param Storage $storage
     * @param callable $proceed
     * @param string $source
     * @param bool $keepRatio
     * @return mixed
     */
    public function aroundResizeFile(Storage $storage, callable $proceed, $source, $keepRatio = true)
    {
        return $this->checkAndProcess($proceed, $source, $keepRatio);
    }

    /**
     * Return original file path as thumbnail for vector images
     *
     * @param Storage $storage
     * @param callable $proceed
     * @param string $filePath
     * @param bool $checkFile
     * @return string
     */
    public function aroundGetThumbnailPath(Storage $storage, callable $proceed, $filePath, $checkFile = false)
    {
        return $this->checkAndProcess($proceed, $filePath, $checkFile);
    }

    /**
     * Check and process
     *
     * @param callable $proceed
     * @param string $filePath
     * @param bool $checkFile
     * @return mixed
     */
    public function checkAndProcess($proceed, $filePath, $checkFile)
    {
        if ($this->helperData->isVectorImage($filePath)) {
            return $filePath;
        }

        if ($this->helperData->isWebImage($filePath)) {
            return $filePath;
        }

        return $proceed($filePath, $checkFile);
    }
}
