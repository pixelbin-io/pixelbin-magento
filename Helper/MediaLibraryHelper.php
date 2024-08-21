<?php

namespace Pixelbinio\Pixelbin\Helper;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Magento\Framework\App\Helper\Context;

class MediaLibraryHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ConfigurationInterface
     */
    protected $helperData;

    /**
     * Cloudinary credentials
     * @var array|null
     */
    protected $credentials;

    /**
     * Current timestamp
     * @var int|null
     */
    protected $timestamp;

    /**
     * Sugnature
     * @var string|null
     */
    protected $signature;

    /**
     * Cloudinary ML Options
     * @var array|null
     */
    protected $cloudinaryMLoptions;

    /**
     * @param Context $context
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        HelperData $helperData
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
    }

    /**
     * @method getCloudinaryMLOptions
     * @param bool $multiple Allow multiple
     * @param bool $refresh Refresh options
     * @return array
     */
    public function getCloudinaryMLOptions($multiple = false, $refresh = true)
    {
        if ((is_null($this->cloudinaryMLoptions) || $refresh) && $this->helperData->isModuleEnabled()) {
            $this->cloudinaryMLoptions = [];
            $this->timestamp = time();
            
            if (!$this->helperData->getAppCloudName() || !$this->helperData->getAppApiSecret()) {
                
            } else {
                $this->cloudinaryMLoptions = [
                    'cloud_name' => $this->helperData->getAppCloudName(),
                    'api_key' => $this->helperData->getAppApiSecret(),
                    'cms_type' => 'magento',
                    //'default_transformations' => [['quality' => 'auto'],['format' => 'auto']],
                    'integration' => [
                        'type' => 'magento_plugin',
                        'platform' => ""
                    ]
                ];
            }
        }
        if ($this->cloudinaryMLoptions) {
            $this->cloudinaryMLoptions['multiple'] = $multiple;
        }

        return $this->cloudinaryMLoptions;
    }

    /**
     * @method getCloudinaryMLshowOptions
     * @param  string|null $resourceType
     * @param  string $path
     * @return [type]
     */
    public function getCloudinaryMLshowOptions($resourceType = null, $path = "")
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

    /**
     * @return null
     */
    public function getCname()
    {
        return $this->helperData->getAppCloudName();
    }
}
