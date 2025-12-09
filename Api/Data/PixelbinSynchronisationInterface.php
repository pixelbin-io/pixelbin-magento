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
 * @api
 */

declare(strict_types=1);

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
     * @api
     */
    public function getEntityId();

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return $this
     * @api
     */
    public function setEntityId($entityId);

    /**
     * Get Image Path
     *
     * @return string
     * @api
     */
    public function getImagePath();

    /**
     * Set Image Path
     *
     * @param string $imagePath
     * @return $this
     * @api
     */
    public function setImagePath($imagePath);

    /**
     * Get File Data
     *
     * @return string
     * @api
     */
    public function getFileData();

    /**
     * Set File Data
     *
     * @param string $fileData
     * @return $this
     * @api
     */
    public function setFileData($fileData);

    /**
     * Get Sync status
     *
     * @return string
     * @api
     */
    public function getSyncStatus();

    /**
     * Set Sync status
     *
     * @param string $syncStatus
     * @return $this
     * @api
     */
    public function setSyncStatus($syncStatus);

    /**
     * Get Error Message
     *
     * @return string
     * @api
     */
    public function getErrorMessage();

    /**
     * Set Error Message
     *
     * @param string $errorMessage
     * @return $this
     * @api
     */
    public function setErrorMessage($errorMessage);

    /**
     * Get Created At
     *
     * @return string
     * @api
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     * @api
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string
     * @api
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updateAt
     * @return $this
     * @api
     */
    public function setUpdatedAt($updateAt);
}