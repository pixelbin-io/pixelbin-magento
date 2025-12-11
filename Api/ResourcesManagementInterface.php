<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
