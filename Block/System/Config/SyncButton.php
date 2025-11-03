<?php

namespace Pixelbinio\Pixelbin\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncStatus;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\Collection;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\CollectionFactory as PixelbinSyncCollectionFactory;

class SyncButton extends Field
{
    protected const SYNC_BUTTON_TEMPLATE = 'system/config/sync_button.phtml';

    /**
     * @var PixelbinSyncCollectionFactory
     */
    protected $pixelbinSyncCollectionFactory;

    /**
     * @param Context $context
     * @param PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->pixelbinSyncCollectionFactory = $pixelbinSyncCollectionFactory;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::SYNC_BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render phtml
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'sync_image',
                'button_label' => __('Sync Image'),
                'onclick' => $this->getAjaxCheckUrl()
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('pixelbin/images/sync');
    }

    /**
     * Get Total sync collection
     *
     * @return Collection
     */
    public function getTotalSyncCollection()
    {
        return $this->pixelbinSyncCollectionFactory->create();
    }

    /**
     * Get pending sync collection
     *
     * @return Collection
     */
    public function getPendingSyncCollection()
    {
        return $this->pixelbinSyncCollectionFactory->create()
            ->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                SyncStatus::STATUS_PENDING
            );
    }

    /**
     * Get failed sync collection
     *
     * @return Collection
     */
    public function getFiledSyncCollection()
    {
        return $this->pixelbinSyncCollectionFactory->create()
            ->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                SyncStatus::STATUS_ERROR
            );
    }

    /**
     * Get success sync collection
     *
     * @return Collection
     */
    public function getSuccessSyncCollection()
    {
        return $this->pixelbinSyncCollectionFactory->create()
            ->addFieldToFilter(
                PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                SyncStatus::STATUS_SYNCED
            );
    }
}
