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
 * @version     1.0.1
 */

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
