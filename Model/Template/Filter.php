<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\Template;

use Magento\Widget\Model\Template\Filter as WidgetFilter;

class Filter extends WidgetFilter
{
    /**
     * Return associative array of parameters *exposing $this->getParameters().
     *
     * @param  string $value raw parameters
     * @return array
     */
    public function getParams($value)
    {
        return $this->getParameters($value);
    }
}
