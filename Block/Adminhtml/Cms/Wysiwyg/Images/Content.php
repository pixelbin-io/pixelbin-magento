<?php
/**
 * Pixelbinio
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Pixelbinio
 * @package     Pixelbinio_Pixelbin
 */
declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Block\Adminhtml\Cms\Wysiwyg\Images;

use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Helper\MediaLibraryHelper;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Wysiwyg Images content block
 *
 * @api
 * @since 100.0.2
 */
class Content extends \Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content
{
    /**
     * @var array|null
     */
    protected $mediaLibraryHelper;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param MediaLibraryHelper $mediaLibraryHelper
     * @param ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        MediaLibraryHelper $mediaLibraryHelper,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $data);
        $this->mediaLibraryHelper = $mediaLibraryHelper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get pixelbin media library widget options
     *
     * @param bool $multiple Allow multiple
     * @param bool $refresh Refresh options
     * @return string
     * @throws \Exception
     */
    public function getPixelbinMediaLibraryWidgetOptions($multiple = false, $refresh = false)
    {
        if (!($pixelbinOptions = $this->mediaLibraryHelper->getPixelbinOptions($multiple, $refresh))) {
            return null;
        }
        try {
            if (version_compare($this->productMetadata->getVersion(), '2.3.5', '<=')) {
                // @codingStandardsIgnoreLine
                $imageUploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('pixelbin/cms_wysiwyg_images/upload', ['type' => $this->_getMediaType()]);
            } else {
                // @codingStandardsIgnoreLine
                $imageUploadUrl = $this->_urlBuilder->getUrl('pixelbin/cms_wysiwyg_images/upload', ['type' => $this->_getMediaType()]);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        }

        return $this->_jsonEncoder->encode(
            [
                'htmlId' => $this->getHtmlId(),
                'cldMLid' => 'wysiwyg_media_gallery',
                'cloud_name' => $pixelbinOptions["cloud_name"] ?? "",
                'remove_header' => true,
                'max_files' => "1",
                'insert_caption' => "Insert",
                'inline_container' => false,
                'default_transformations' => [[]],
                'button_class' => 'add_from_pixelbin',
                'button_caption' => '',
                'imageUploaderUrl' => $imageUploadUrl,
                'triggerSelector' => '.media-gallery-modal',
                'triggerEvent' => 'fileuploaddone',
                'pixelbinMLoptions' => $pixelbinOptions,
                'addTmpExtension' => false,
                'pixelbin_options' => $pixelbinOptions,
                'pixelbinShowOptions' => $this->mediaLibraryHelper->getPixelbinShowOptions("image"),
            ]
        );
    }

    /**
     * Return current media type based on request or data
     *
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
