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

namespace Pixelbinio\Pixelbin\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\ProductVideo\Helper\Media;
use Magento\Store\Model\StoreManagerInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewVideo extends \Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @method __construct
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Media $mediaHelper
     * @param EncoderInterface $jsonEncoder
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        FormFactory           $formFactory,
        Media                 $mediaHelper,
        EncoderInterface      $jsonEncoder,
        HelperData            $helperData,
        StoreManagerInterface $storeManager,
        array                 $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $mediaHelper,
            $jsonEncoder,
            $data
        );
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * Get widget options
     *
     * @return string
     */
    public function getWidgetOptions()
    {
        return $this->jsonEncoder->encode(
            [
                'saveVideoUrl' => $this->getUrl('catalog/product_gallery/upload'),
                'saveRemoteVideoUrl' => $this->getUrl('pixelbin/ajax/retrieveImage'),
                'htmlId' => $this->getHtmlId(),
                'youTubeApiKey' => $this->mediaHelper->getYouTubeApiKey(),
                'videoSelector' => $this->videoSelector,
                'pixelbinPlaceholder' => $this->getPlaceholderUrl(),
            ]
        );
    }

    /**
     * Get note for video url
     *
     * @return string
     */
    protected function getNoteVideoUrl()
    {
        if (!$this->helperData->isModuleEnabled()) {
            return parent::getNoteVideoUrl();
        }

        $result = __('Supported: Vimeo');
        $messages = "";
        if ($this->mediaHelper->getYouTubeApiKey() === null) {
            $messages .= __('<br>*To add YouTube video, please <a href="%1">enter YouTube API Key</a> first.', $this->getConfigApiKeyUrl());
        } else {
            $result .= __(', YouTube');
        }

        if (!$this->helperData->isModuleEnabled()) {
            $messages .= __('<br>*To add Pixelbin video, please <a href="%1">configure your module from configuration</a> first.', $this->getPixelbinConfigUrl());
        } else {
            $result .= __(', Pixelbin');
        }

        return $result . $messages;
    }

    /**
     * Get url for Cloudinary config params
     *
     * @return string
     */
    protected function getPixelbinConfigUrl()
    {
        return $this->urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            [
                'section' => 'pixelbinio'
            ]
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPlaceholderUrl()
    {
        $storeManager = $this->storeManager;
        $configPaths = [
            'catalog/placeholder/image_placeholder',
            'catalog/placeholder/small_image_placeholder',
            'catalog/placeholder/thumbnail_placeholder',
        ];
        foreach ($configPaths as $configPath) {
            if (($path = $storeManager->getStore()->getConfig($configPath))) {
                return $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/' . $path;
                break;
            }
        }
        return $this->getViewFileUrl('Pixelbinio_Pixelbin::images/pixelbin_cloud_glyph_blue.png');
    }
}
