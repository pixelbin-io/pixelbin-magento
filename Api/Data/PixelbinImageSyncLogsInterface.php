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

interface PixelbinImageSyncLogsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const TABLE_NAME = "pixelbin_image_sync_logs";

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
     * Get Request
     *
     * @return string
     * @api
     */
    public function getRequest();

    /**
     * Set Request
     *
     * @param string $request
     * @return $this
     * @api
     */
    public function setRequest($request);

    /**
     * Get Response
     *
     * @return string
     * @api
     */
    public function getResponse();

    /**
     * Set Response
     *
     * @param string $response
     * @return $this
     * @api
     */
    public function setResponse($response);

    /**
     * Get Sync Type
     *
     * @return string
     * @api
     */
    public function getSyncType();

    /**
     * Set Sync Type
     *
     * @param string $syncType
     * @return $this
     * @api
     */
    public function setSyncType($syncType);

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
