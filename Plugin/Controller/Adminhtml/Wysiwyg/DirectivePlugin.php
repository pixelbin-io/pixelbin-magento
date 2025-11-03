<?php

namespace Pixelbinio\Pixelbin\Plugin\Controller\Adminhtml\Wysiwyg;

use Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive;
use Magento\Framework\Controller\Result\Raw;
use Pixelbinio\Pixelbin\Plugin\AbstractPixelbinPlugin;

class DirectivePlugin extends AbstractPixelbinPlugin
{
    /**
     * Handle vector images for media storage thumbnails
     *
     * @param Directive $subject
     * @param callable $proceed
     * @return Raw
     */
    public function aroundExecute(Directive $subject, callable $proceed)
    {
        try {
            $directive = $subject->getRequest()->getParam('___directive');
            $directive = $this->urlDecoder->decode($directive);
            $imagePath = $this->filter->filter($directive);
            return $this->saveSvgVectorImage($imagePath);
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
