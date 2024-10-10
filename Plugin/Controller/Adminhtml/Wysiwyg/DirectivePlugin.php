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

namespace Pixelbinio\Pixelbin\Plugin\Controller\Adminhtml\Wysiwyg;

use Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive;
use Magento\Cms\Model\Template\Filter;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\DecoderInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class DirectivePlugin
{
    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * DirectivePlugin constructor.
     *
     * @param DecoderInterface $urlDecoder
     * @param Filter $filter
     * @param RawFactory $resultRawFactory
     * @param HelperData $helperData
     */
    public function __construct(
        DecoderInterface $urlDecoder,
        Filter $filter,
        RawFactory $resultRawFactory,
        HelperData $helperData
    ) {
        $this->urlDecoder = $urlDecoder;
        $this->filter = $filter;
        $this->resultRawFactory = $resultRawFactory;
        $this->helperData = $helperData;
    }

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

            if (!$this->helperData->isVectorImage($imagePath)) {
                throw new LocalizedException(__('This is not a vector image'));
            }

            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', 'image/svg+xml');
            $resultRaw->setContents(file_get_contents($imagePath));

            return $resultRaw;
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
