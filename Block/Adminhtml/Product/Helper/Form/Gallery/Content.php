<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Block\Adminhtml\Product\Helper\Form\Gallery;

use Pixelbinio\Pixelbin\Helper\MediaLibraryHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

/**
 * Block for gallery content.
 */
class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    /**
     * @var string
     */
    protected $_template = 'Pixelbinio_Pixelbin::catalog/product/gallery.phtml';

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var MediaLibraryHelper
     */
    protected $mediaLibraryHelper;

    /**
     * @method __construct
     * @param  Context                  $context
     * @param  EncoderInterface         $jsonEncoder
     * @param  DecoderInterface         $jsonDecoder
     * @param  Config                   $mediaConfig
     * @param  MediaLibraryHelper       $mediaLibraryHelper
     * @param  array                    $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        Config $mediaConfig,
        MediaLibraryHelper $mediaLibraryHelper,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $mediaConfig, $data);
        $this->jsonDecoder = $jsonDecoder;
        $this->mediaLibraryHelper = $mediaLibraryHelper;
    }

    /**
     * Get Pixelbin media library widget options
     *
     * @param bool $multiple Allow multiple
     * @param bool $refresh Refresh options
     * @return string
     */
    public function getPixelbinMediaLibraryWidgetOptions($multiple = true, $refresh = false)
    {
        try {
            //Try to add session param on Magento versions prior to 2.3.5
            $imageUploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('pixelbin/ajax/retrieveImage');
        } catch (\Exception $e) {
            //Catch deprecation error on Magento 2.3.5 and above
            $imageUploadUrl = $this->_urlBuilder->getUrl('pixelbin/ajax/retrieveImage');
        }
        $pixelbinOptions = $this->mediaLibraryHelper->getPixelbinOptions(null);
        return $this->_jsonEncoder->encode(
            [
                'htmlId' => $this->getHtmlId(),
                'cldMLid' => 'product_gallery_' . $this->getHtmlId(),
                'cloud_name' => $pixelbinOptions["cloud_name"] ?? "",
                'remove_header' => true,
                'max_files' => "1",
                'insert_caption' => "Insert",
                'inline_container' => false,
                'default_transformations' => [[]],
                'button_class' => 'gallery_inner_add_from_pixelbin_placeholder_button_' . $this->getHtmlId(),
                'button_caption' => '',
                'imageUploaderUrl' => $imageUploadUrl,
                'triggerSelector' => '#media_gallery_content',
                'triggerEvent' => 'addItem',
                'useDerived' => false,
                'addTmpExtension' => true,
                'pixelbin_options' => $pixelbinOptions,
                'pixelbinShowOptions' => $this->mediaLibraryHelper->getPixelbinShowOptions(null),
            ]
        );
    }

    /**
     * Escape a string for the HTML attribute context
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if (method_exists($this->_escaper, 'escapeHtmlAttr')) {
            return $this->_escaper->escapeHtmlAttr($string, $escapeSingleQuote);
        }
        if ($escapeSingleQuote) {
            $escaper = new \Laminas\Escaper\Escaper();
            return $escaper->escapeHtmlAttr((string) $string);
        }
        $escaper = new \Laminas\Escaper\Escaper();
        return $escaper->escapeHtml((string)$string);
    }
}
