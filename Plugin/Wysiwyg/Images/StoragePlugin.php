<?php
/**
 * Iksula
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Pixelbinio
 * @package     Pixelbinio_Pixelbin
 * @version     1.0.0
 */

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
        if ($this->helperData->isVectorImage($source)) {
            return $source;
        }

        return $proceed($source, $keepRatio);
    }

    /**
     * Return original file path as thumbnail for vector images
     *
     * @param Storage $storage
     * @param callable $proceed
     * @param string $filePath
     * @param bool $checkFile
     */
    public function aroundGetThumbnailPath(Storage $storage, callable $proceed, $filePath, $checkFile = false)
    {
        if ($this->helperData->isVectorImage($filePath)) {
            return $filePath;
        }

        return $proceed($filePath, $checkFile);
    }
}
