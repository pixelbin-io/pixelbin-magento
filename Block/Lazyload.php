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

namespace Pixelbinio\Pixelbin\Block;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Lazyload extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @method __construct
     * @param  Context                $context
     * @param  HelperData             $helperData,
     * @param  EncoderInterface       $jsonEncoder
     * @param  array                  $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Is Enabled Lazy Load
     *
     * @method isEnabledLazyload
     * @return boolean
     */
    public function isEnabledLazyload()
    {
        return $this->helperData->isModuleEnabled() && $this->helperData->isEnabledLazyload();
    }

    /**
     * Get lazy load options
     *
     * @method getLazyloadOptions
     * @param  boolean            $json
     * @return string|array
     */
    public function getLazyloadOptions($json = true)
    {
        $options = [
            'threshold' => $this->helperData->getLazyloadThreshold(),
            'effect' => $this->helperData->getLazyloadEffect(),
            'placeholder' => $this->helperData->getLazyloadPlaceholder(),
        ];
        return $json ? $this->jsonEncoder->encode($options) : $options;
    }
}
