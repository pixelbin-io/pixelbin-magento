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
 * @version     1.0.1
 */

namespace Pixelbinio\Pixelbin\Plugin\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail;
use Magento\Framework\Controller\Result\Raw;
use Pixelbinio\Pixelbin\Plugin\AbstractPixelbinPlugin;

class ThumbnailPlugin extends AbstractPixelbinPlugin
{

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
            return $this->saveSvgVectorImage($thumb);
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
