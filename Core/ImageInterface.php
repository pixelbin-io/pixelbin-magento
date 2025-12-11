<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Core;

interface ImageInterface
{
    /**
     * It allows to be treated like a string
     *
     * @return string
     */
    public function __toString();
}
