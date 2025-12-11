<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\MediaStorage\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;

class Uploader
{
    public const ALLOWED_EXTENSIONS = ['png', 'gif', 'jpg', 'jpeg'];

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     * @param HelperData $helperData
     * @param DirectoryList $directoryList
     */
    public function __construct(
        UploadFileToPixelbin $uploadFileToPixelbin,
        HelperData $helperData,
        DirectoryList $directoryList
    ) {
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->helperData = $helperData;
        $this->directoryList = $directoryList;
    }

    /**
     * After save image
     *
     * @param \Magento\Framework\File\Uploader $subject
     * @param array $result
     * @return array
     */
    public function afterSave($subject, $result)
    {
        $this->uploadFileToPixelbin->fileUploadAfterSave($result);
        return $result;
    }

    /**
     * Value for allowed image extension
     *
     * @param  string $filepath
     * @return string
     */
    protected function isAllowedImageExtension($filepath)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return in_array(pathinfo($filepath, PATHINFO_EXTENSION), self::ALLOWED_EXTENSIONS);
    }

    /**
     * Value for media file path
     *
     * @param string $filepath
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function isMediaFilePath($filepath)
    {
        return strpos($filepath, $this->directoryList->getPath('media')) === 0;
    }

    /**
     * Value for media temp file path
     *
     * @param string $filepath
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function isMediaTmpFilePath($filepath)
    {
        return strpos($filepath, sprintf('%s/tmp', $this->directoryList->getPath('media'))) === 0;
    }

    /**
     * Value for absolute file path
     *
     * @param  array $result
     * @return string
     */
    protected function absoluteFilePath(array $result)
    {
        return sprintf('%s%s%s', $result['path'], DIRECTORY_SEPARATOR, $result['file']);
    }

    /**
     * Value for media relative path
     *
     * @param string $filepath
     * @return array|mixed|string|string[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function mediaRelativePath($filepath)
    {
        $pubPath = $this->directoryList->getPath(DirectoryList::PUB) . DIRECTORY_SEPARATOR;
        return (strpos($filepath, $pubPath) === 0) ? str_replace($pubPath, '', $filepath) : $filepath;
    }
}
