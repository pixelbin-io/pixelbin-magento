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

namespace Pixelbinio\Pixelbin\Plugin\Cms\Block\Widget;

use Pixelbinio\Pixelbin\Plugin\CmsBlockLazyloadAbstract;
use Magento\Cms\Block\Widget\Block as CmsBlockWidget;

/**
 * Plugin for CMS Widget Block lazy loading
 * 
 * Note: This class appears similar to other CMS block plugins in this module.
 * Separate plugin classes are required by Magento's plugin architecture to
 * properly intercept different block types with correct type hints.
 * All business logic is centralized in CmsBlockLazyloadAbstract.
 */
class Block extends CmsBlockLazyloadAbstract
{
    /**
     * After to html
     *
     * @param CmsBlockWidget $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml(CmsBlockWidget $subject, $html)
    {
        return $this->process($subject, $html);
    }
}
