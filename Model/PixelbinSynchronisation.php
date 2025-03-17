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

namespace Pixelbinio\Pixelbin\Model;

use Magento\Framework\Exception\LocalizedException;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation as PixelbinSynchronisationResourceModel;
use Magento\Framework\App\ResourceConnection;

class PixelbinSynchronisation extends \Magento\Framework\Model\AbstractModel implements PixelbinSynchronisationInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * PixelbinSynchronisation construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(PixelbinSynchronisationResourceModel::class);
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get Truncate table
     *
     * @return $this
     * @throws LocalizedException
     */
    public function truncateTable($tableName)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $fullTableName = $this->resourceConnection->getTableName($tableName);
            $connection->truncateTable($fullTableName);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        }
        return $this;
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
    public function getImagePath()
    {
        return $this->getData(self::KEY_IMAGE_PATH);
    }

    /**
     * @inheritDoc
     */
    public function setImagePath($imagePath)
    {
        $this->setData(self::KEY_IMAGE_PATH, $imagePath);
    }

    /**
     * @inheritDoc
     */
    public function getFileData()
    {
        return $this->getData(self::KEY_FILE_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setFileData($fileData)
    {
        $this->setData(self::KEY_FILE_DATA, $fileData);
    }

    /**
     * @inheritDoc
     */
    public function getSyncStatus()
    {
        return $this->getData(self::KEY_SYNC_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setSyncStatus($syncStatus)
    {
        $this->setData(self::KEY_SYNC_STATUS, $syncStatus);
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return $this->getData(self::KEY_ERROR_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorMessage($errorMessage)
    {
        $this->setData(self::KEY_ERROR_MESSAGE, $errorMessage);
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
