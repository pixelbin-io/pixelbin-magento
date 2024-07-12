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

interface PixelbinImageSyncLogsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const TABLE_NAME = "pixelbin_synchronisation";

    public const KEY_ENTITY_ID = "entity_id";
    public const KEY_REQUEST = "request";
    public const KEY_RESPONSE = "response";
    public const KEY_SYNC_TYPE = "sync_type";
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
     * Set Request
     *
     * @return string
     */
    public function getRequest();

    /**
     * Get Request
     *
     * @param string $request
     * @return $this
     */
    public function setRequest($request);

    /**
     * Set Response
     *
     * @return string
     */
    public function getResponse();

    /**
     * Get Response
     *
     * @param string $response
     * @return $this
     */
    public function setResponse($response);

    /**
     * Set Sync Type
     *
     * @return string
     */
    public function getSyncType();

    /**
     * Get Sync Type
     *
     * @param string $syncType
     * @return $this
     */
    public function setSyncType($syncType);

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
