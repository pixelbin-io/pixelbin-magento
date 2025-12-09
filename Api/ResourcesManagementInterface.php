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

namespace Pixelbinio\Pixelbin\Api;

interface ResourcesManagementInterface
{
    /**
     * GET for getImage api
     *
     * @return string
     * @api
     */
    public function getImage();

    /**
     * GET for getVideo api
     *
     * @return string
     * @api
     */
    public function getVideo();

    /**
     * GET for getResourcesByTag api
     *
     * @return string
     * @api
     */
    public function getResourcesByTag();
}
