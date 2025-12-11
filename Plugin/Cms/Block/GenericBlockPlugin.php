<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Plugin\Cms\Block;

use Pixelbinio\Pixelbin\Plugin\Cms\CmsBlockLazyloadAbstract;

/**
 * Generic Plugin for CMS Block and Widget Block lazy loading
 * Consolidates functionality from both Block.php and Widget/Block.php
 */
class GenericBlockPlugin extends CmsBlockLazyloadAbstract
{
    // Intentionally empty – logic inherited from CmsBlockLazyloadAbstract
    // This plugin can be used for both CMS Block and Widget Block classes
}
