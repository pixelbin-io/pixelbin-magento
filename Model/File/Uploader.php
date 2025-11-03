<?php

namespace Pixelbinio\Pixelbin\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * Uploader constructor.
     *
     * @param HelperData $helperData
     * @param string $fileId
     * @param Mime|null $fileMime
     * @param DirectoryList|null $directoryList
     * @param DriverPool|null $driverPool
     * @param TargetDirectory|null $targetDirectory
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        HelperData $helperData,
        string $fileId,
        ?Mime $fileMime = null,
        ?DirectoryList $directoryList = null,
        ?DriverPool $driverPool = null,
        ?TargetDirectory $targetDirectory = null,
        ?Filesystem $filesystem = null
    ) {
        parent::__construct(
            $fileId,
            $fileMime,
            $directoryList,
            $driverPool,
            $targetDirectory,
            $filesystem
        );

        $this->helperData = $helperData;
    }

    /**
     * Add web images to the list of allowed Mime-Types
     *
     * @param array $validTypes
     * @return bool
     */
    public function checkMimeType($validTypes = [])
    {
        foreach ($this->helperData->getVectorExtensions() as $extension) {
            $validTypes[] = 'image/' . $extension;
        }

        foreach ($this->helperData->getWebImageExtensions() as $extension) {
            $validTypes[] = 'image/' . $extension;
        }

        return parent::checkMimeType($validTypes);
    }

    /**
     * Add web images to the list of allowed extensions
     *
     * @param array $extensions
     * @return Uploader
     */
    public function setAllowedExtensions($extensions = [])
    {
        $extensions = array_merge(
            $extensions,
            $this->helperData->getVectorExtensions(),
            $this->helperData->getWebImageExtensions()
        );

        return parent::setAllowedExtensions($extensions);
    }
}
