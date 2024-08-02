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

namespace Pixelbinio\Pixelbin\Api\Data;

interface PixelbinSynchronisationInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const TABLE_NAME = "pixelbin_synchronisation";

    public const KEY_ENTITY_ID = "entity_id";
    public const KEY_IMAGE_PATH = "image_path";
    public const KEY_FILE_DATA = "file_data";
    public const KEY_SYNC_STATUS = "sync_status";
    public const KEY_ERROR_MESSAGE = "error_message";
    public const KEY_CREATED_AT = "created_at";
    public const KEY_UPDATED_AT = "updated_at";

    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Set Image Path
     *
     * @return string
     */
    public function getImagePath();

    /**
     * Get Image Path
     *
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath);

    /**
     * Set File Data
     *
     * @return string
     */
    public function getFileData();

    /**
     * Get File Data
     *
     * @param string $fileData
     * @return $this
     */
    public function setFileData($fileData);

    /**
     * Set Sync status
     *
     * @return string
     */
    public function getSyncStatus();

    /**
     * Get Sync status
     *
     * @param string $syncStatus
     * @return $this
     */
    public function setSyncStatus($syncStatus);

    /**
     * Set Error Message
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * Get Error Message
     *
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage);

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updateAt
     * @return $this
     */
    public function setUpdatedAt($updateAt);
}
