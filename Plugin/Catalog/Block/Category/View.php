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
