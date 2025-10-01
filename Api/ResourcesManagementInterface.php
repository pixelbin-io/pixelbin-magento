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

namespace Pixelbinio\Pixelbin\Api;

interface ResourcesManagementInterface
{

    /**
     * GET for getImage api
     *
     * @return string
     */
    public function getImage();

    /**
     * GET for getVideo api
     *
     * @return string
     */
    public function getVideo();

    /**
     * GET for getSpinestFirstImage api
     *
     * @return string
     */
    public function getResourcesByTag();
}
