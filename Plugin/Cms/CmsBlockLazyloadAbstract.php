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

namespace Pixelbinio\Pixelbin\Plugin\Cms;

abstract class CmsBlockLazyloadAbstract
{
    /**
     * Common afterToHtml plugin handler
     *
     * @param mixed $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml($subject, string $html): string
    {
        return $this->process($subject, $html);
    }

    /**
     * Shared lazyload logic
     *
     * @param mixed $subject
     * @param string $html
     * @return string
     */
    protected function process($subject, string $html): string
    {
        return $html;
    }
}
