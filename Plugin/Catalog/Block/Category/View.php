<?php

namespace Pixelbinio\Pixelbin\Plugin\Catalog\Block\Category;

use Pixelbinio\Pixelbin\Plugin\CmsBlockLazyloadAbstract;
use Magento\Catalog\Block\Category\View as CatalogCategoryBlock;

/**
 * Class View
 */
class View extends CmsBlockLazyloadAbstract
{
    /**
     * @method afterGetCmsBlockHtml
     * @param  CatalogCategoryBlock $subject
     * @param  string               $html
     * @return string
     */
    public function afterGetCmsBlockHtml(CatalogCategoryBlock $subject, $html)
    {
        return $this->process($subject, $html);
    }
}
