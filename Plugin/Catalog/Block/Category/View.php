<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Plugin\Catalog\Block\Category;

use Pixelbinio\Pixelbin\Plugin\CmsBlockLazyloadAbstract;
use Magento\Catalog\Block\Category\View as CatalogCategoryBlock;

class View extends CmsBlockLazyloadAbstract
{
    /**
     * Build image html
     *
     * @param  CatalogCategoryBlock $subject
     * @param  string               $html
     * @return string
     */
    public function afterGetCmsBlockHtml(CatalogCategoryBlock $subject, $html)
    {
        return $this->process($subject, $html);
    }
}
