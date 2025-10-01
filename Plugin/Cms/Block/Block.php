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

namespace Pixelbinio\Pixelbin\Plugin\Cms\Block;

use Pixelbinio\Pixelbin\Plugin\CmsBlockLazyloadAbstract;
use Magento\Cms\Block\Block as CmsBlockBlock;

class Block extends CmsBlockLazyloadAbstract
{

    /**
     * After to html
     *
     * @param CmsBlockBlock $subject
     * @param string $html
     * @return false|mixed|string
     */
    public function afterToHtml(CmsBlockBlock $subject, $html)
    {
        return $this->process($subject, $html);
    }
}
