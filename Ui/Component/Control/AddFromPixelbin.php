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

namespace Pixelbinio\Pixelbin\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Pixelbinio\Pixelbin\Block\Adminhtml\Cms\Wysiwyg\Images\Content;
use Magento\Framework\AuthorizationInterface;

class AddFromPixelbin implements ButtonProviderInterface
{
    private const ACL_UPLOAD_ASSETS= 'Magento_MediaGalleryUiApi::upload_assets';

    /**
     * @var Content
     */
    protected $images;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $cmsWysiwygImages;

    /**
     * @param Content $images
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Content $images,
        AuthorizationInterface $authorization,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
    ) {
        $this->images = $images;
        $this->authorization =  $authorization;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
    }
    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $pixelbinMLwidgetOprions = json_decode($this->images->getPixelbinMediaLibraryWidgetOptions(), true);


        $buttonData = [
            'label' => __('Add From Pixelbin'),
            'class' => 'action-secondary add-from-pixelbin-button pixelbin-button-with-logo lg-margin-bottom',
            'on_click' => 'return false;',
            'data_attribute' => [
                'mage-init' => ['pixelbinMediaLibraryModal' => $pixelbinMLwidgetOprions],
                'role' => 'add-from-pixelbin-button',

            ],
            'sort_order' => 200,
        ];

        if (!$this->authorization->isAllowed(self::ACL_UPLOAD_ASSETS)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
