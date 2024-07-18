<?php

namespace Pixelbinio\Pixelbin\Plugin\Cms\Block\Widget;

use Pixelbinio\Pixelbin\Plugin\CmsBlockLazyloadAbstract;
use Magento\Cms\Block\Widget\Block as CmsBlockWidget;

/**
 * Class Blocks
 */
class Block extends CmsBlockLazyloadAbstract
{

    /**
     * @method afterToHtml
     * @param  CmsBlockWidget $subject
     * @param  string         $html
     * @return string
     */
    public function afterToHtml(CmsBlockWidget $subject, $html)
    {
        return $this->process($subject, $html);
    }
}
