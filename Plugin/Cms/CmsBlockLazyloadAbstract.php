<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
