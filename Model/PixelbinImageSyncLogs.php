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

namespace Pixelbinio\Pixelbin\Model;

use Pixelbinio\Pixelbin\Api\Data\PixelbinImageSyncLogsInterface;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinImageSyncLogs as PixelbinImageSyncLogsResourceModel;

class PixelbinImageSyncLogs extends \Magento\Framework\Model\AbstractModel implements PixelbinImageSyncLogsInterface
{
    /**
     * PixelbinImageSyncLogs construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(PixelbinImageSyncLogsResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::KEY_ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(self::KEY_ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getRequest()
    {
        return $this->getData(self::KEY_REQUEST);
    }

    /**
     * @inheritDoc
     */
    public function setRequest($request)
    {
        $this->setData(self::KEY_REQUEST, $request);
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->getData(self::KEY_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setResponse($response)
    {
        $this->setData(self::KEY_RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getSyncType()
    {
        return $this->getData(self::KEY_SYNC_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setSyncType($syncType)
    {
        $this->setData(self::KEY_SYNC_TYPE, $syncType);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updateAt)
    {
        $this->setData(self::KEY_UPDATED_AT, $updateAt);
    }
}
