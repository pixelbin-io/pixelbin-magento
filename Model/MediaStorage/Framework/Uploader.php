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

namespace Pixelbinio\Pixelbin\Model\MediaStorage\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;

class Uploader
{
    const ALLOWED_EXTENSIONS = ['png', 'gif', 'jpg', 'jpeg'];

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
        HelperData           $helperData,
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
        if ($this->helperData->isModuleEnabled()) {
            if (!empty($result) && !empty($result['path']) && !empty($result['file'])) {
                $filePath = $result['path'] . '/' . $result['file'];
                if (!str_contains($filePath, 'tmp/')) {
                    $this->uploadFileToPixelbin->cmsUploadFileSync($result);
                }
            }
        }
        return $result;
    }

    /**
     * @param  string $filepath
     * @return string
     */
    protected function isAllowedImageExtension($filepath)
    {
        return in_array(pathinfo($filepath, PATHINFO_EXTENSION), self::ALLOWED_EXTENSIONS);
    }

    /**
     * @param  string $filepath
     * @return bool
     */
    protected function isMediaFilePath($filepath)
    {
        return strpos($filepath, $this->directoryList->getPath('media')) === 0;
    }

    /**
     * @param  string $filepath
     * @return string
     */
    protected function isMediaTmpFilePath($filepath)
    {
        return strpos($filepath, sprintf('%s/tmp', $this->directoryList->getPath('media'))) === 0;
    }

    /**
     * @param  array $result
     * @return string
     */
    protected function absoluteFilePath(array $result)
    {
        return sprintf('%s%s%s', $result['path'], DIRECTORY_SEPARATOR, $result['file']);
    }

    /**
     * @param  string $filepath
     * @return string
     */
    protected function mediaRelativePath($filepath)
    {
        $pubPath = $this->directoryList->getPath(DirectoryList::PUB) . DIRECTORY_SEPARATOR;
        return (strpos($filepath, $pubPath) === 0) ? str_replace($pubPath, '', $filepath) : $filepath;
    }
}
