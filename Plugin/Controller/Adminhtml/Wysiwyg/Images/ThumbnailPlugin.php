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

namespace Pixelbinio\Pixelbin\Plugin\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class ThumbnailPlugin
{
    /**
     * @var Images
     */
    private $wysiwygImages;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * ThumbnailPlugin constructor.
     *
     * @param Images $wysiwygImages
     * @param RawFactory $resultRawFactory
     * @param HelperData $helperData
     */
    public function __construct(
        Images $wysiwygImages,
        RawFactory $resultRawFactory,
        HelperData $helperData
    ) {
        $this->wysiwygImages = $wysiwygImages;
        $this->resultRawFactory = $resultRawFactory;
        $this->helperData = $helperData;
    }

    /**
     * Handle vector images for media storage thumbnails
     *
     * @param Thumbnail $subject
     * @param callable $proceed
     * @return Raw
     */
    public function aroundExecute(Thumbnail $subject, callable $proceed)
    {
        try {
            $file = $subject->getRequest()->getParam('file');
            $file = $this->wysiwygImages->idDecode($file);
            $thumb = $subject->getStorage()->resizeOnTheFly($file);

            if (!$this->helperData->isVectorImage($thumb)) {
                throw new LocalizedException(__('This is not a vector image'));
            }

            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', 'image/svg+xml');
            $resultRaw->setContents(file_get_contents($thumb));

            return $resultRaw;
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
