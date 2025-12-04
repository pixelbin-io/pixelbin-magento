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

namespace Pixelbinio\Pixelbin\Helper;

use Magento\Framework\App\Helper\Context;

class MediaLibraryHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Pixelbin Options array
     * @var array|null
     */
    protected $pixelbinOptions;

    /**
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Get Pixelbin Options from Config Data
     *
     * @method getPixelbinOptions
     * @param bool $multiple Allow multiple
     * @param bool $refresh Refresh options
     * @return array
     */
    public function getPixelbinOptions($multiple = false, $refresh = true)
    {
        if ($this->helper->isModuleEnabled()) {
            $this->pixelbinOptions = [];
            $this->pixelbinOptions = [
                'cloud_name' => $this->helper->getAppCloudName(),
                'api_key' => $this->helper->getAppApiSecret(),
                'cms_type' => 'magento',
                'integration' => [
                    'type' => 'magento_plugin'
                ]
            ];
        }
        if ($this->pixelbinOptions) {
            $this->pixelbinOptions['multiple'] = $multiple;
        }

        return $this->pixelbinOptions;
    }

    /**
     * Get Path and Resouce type for Pixelbin options
     *
     * @method getPixelbinShowOptions
     * @param string|null $resourceType
     * @param string $path
     * @return [type]
     */
    public function getPixelbinShowOptions($resourceType = null, $path = "")
    {
        $options = [];
        if ($resourceType || $resourceType) {
            $options["folder"] = [
                "path" => $path,
                "resource_type" => $resourceType,
            ];
        }
        return $options;
    }
}
