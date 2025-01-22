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

namespace Pixelbinio\Pixelbin\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException as NotFoundExceptionAlias;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\ResourceConnection;
use Pixelbinio\Pixelbin\Logger\Logger;

class Data extends AbstractHelper
{
    /* Get system config fields */
    public const XML_PATH_EXTENSION_ENABLE = 'pixelbin/general/is_enable';
    public const XML_PATH_APP_CLOUD_NAME = 'pixelbin/app_configuration/cloud_name';
    public const XML_PATH_APP_ZONE = 'pixelbin/app_configuration/zone';
    public const XML_PATH_APP_API_SECRET = 'pixelbin/app_configuration/api_secret';
    public const XML_PATH_SETUP_USE_DEFAULT_IMAGE = 'pixelbin/pixelbin_setup/use_pixelbin_default_image';
    public const XML_PATH_SETUP_DEFAULT_IMAGE = 'pixelbin/pixelbin_setup/default_image';
    public const XML_PATH_AUTO_OPTIMISATION = 'pixelbin/image_transformations/auto_optimisation';
    public const XML_PATH_GLOBAL_CUSTOM_TRANSFORMATION = 'pixelbin/image_transformations/global_custom_transformation';
    //@codingStandardsIgnoreStart
    public const XML_PATH_PRODUCT_CUSTOM_TRANSFORMATION = 'pixelbin/image_transformations/product_custom_transformation';
    //@codingStandardsIgnoreEnd
    public const XML_PATH_MANUAL_CRON_ENABLED = 'pixelbin/pixelbin_setup/pixelbin_image_sync/enable_manual_sync_cron';
    const XML_PATH_VECTOR_EXTENSIONS = 'pixelbin/extensions/vector';
    const XML_PATH_WEB_IMAGE_EXTENSIONS = 'pixelbin/extensions/web_image';
    public const API_URL = "https://api.pixelbin.io";
    public const ZONE_DEFAULT_URL = "https://cdn.pixelbin.io/v2/";
    public const EXCLUDE_FOLDERS = [
        ".thumbscatalog",
        ".thumbswysiwyg",
        "catalog/tmp/",
        "catalog/product/cache/",
    ];
    public const EXCLUDE_EXTENSION = [
        "css",
        "js",
        "htaccess",
        "json",
        "txt",
        "csv",
    ];

    //= Lazyload
    public const XML_PATH_LAZYLOAD_ENABLED = 'pixelbin/lazyload/lazyload_enabled';
    public const XML_PATH_LAZYLOAD_AUTO_REPLACE_CMS_BLOCKS = 'pixelbin/lazyload/is_enable_for_cms_block';
    public const XML_PATH_LAZYLOAD_IGNORED_CMS_BLOCKS = 'pixelbin/lazyload/is_exclude_for_cms_block';
    public const XML_PATH_LAZYLOAD_THRESHOLD = 'pixelbin/lazyload/threshold';
    public const XML_PATH_LAZYLOAD_EFFECT = 'pixelbin/lazyload/effect';
    public const XML_PATH_LAZYLOAD_PLACEHOLDER = 'pixelbin/lazyload/placeholder';
    //@codingStandardsIgnoreStart
    public const LAZYLOAD_DATA_PLACEHOLDER = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';
    //@codingStandardsIgnoreEnd
    public const LIMIT_FOR_CRON = 500;
    public const DEFAULT_PIXELBIN_IMAGE = "code/Pixelbinio/Pixelbin/view/base/web/images/pixelbin_cloud_glyph_blue.png";
    public const PIXELBIN_DEFAULT_IMAGE_URL = "https://cdn.pixelbin.io/v2/dummy-cloudname/original/magento_icons_and_images/pixelbin_logo.png";

    /**
     * @var null
     */
    protected $_appZoneLink = null;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var JsonHelperData
     */
    protected $jsonHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Data construct
     *
     * @param Context $context
     * @param Curl $curl
     * @param CurlFactory $curlFactory
     * @param JsonHelperData $jsonHelper
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context               $context,
        Curl                  $curl,
        CurlFactory           $curlFactory,
        JsonHelperData        $jsonHelper,
        Logger                $logger,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->curlFactory = $curlFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
    }

    /**
     * Get config value from configuration area
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check if Is Module enable
     *
     * @param int $storeId
     * @return mixed
     */
    public function isModuleEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_EXTENSION_ENABLE, $storeId);
    }

    /**
     * Check if cron setting is enabled
     *
     * @param int $storeId
     * @return mixed
     */
    public function isManualSyncCronEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_MANUAL_CRON_ENABLED, $storeId);
    }

    /**
     * Get Pixelbin API url
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return self::API_URL;
    }

    /**
     * Get app cloud name
     *
     * @param int $storeId
     * @return mixed
     */
    public function getAppCloudName($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_APP_CLOUD_NAME, $storeId);
    }

    /**
     * Get app zone
     *
     * @param int $storeId
     * @return mixed
     */
    public function getAppZone($storeId = null)
    {
        if ($this->_appZoneLink === null) {
            $zoneSlug = $this->getConfigValue(self::XML_PATH_APP_ZONE, $storeId);
            if (!empty($zoneSlug)) {
                $this->_appZoneLink = self::ZONE_DEFAULT_URL.$this->getAppCloudName()."/".$zoneSlug."original/";
            }
            $this->_appZoneLink = self::ZONE_DEFAULT_URL.$this->getAppCloudName()."/original/";
        }
        return $this->_appZoneLink;
    }

    /**
     * Get app api secret
     *
     * @param int $storeId
     * @return string|null
     */
    public function getAppApiSecret($storeId = null)
    {
        $encrypted = $this->getConfigValue(self::XML_PATH_APP_API_SECRET, $storeId);
        if (empty($encrypted)) {
            return null;
        }
        return $this->encryptor->decrypt($encrypted);
    }

    /**
     * Get current store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Check if Image Transformation enable
     *
     * @param int $storeId
     * @return mixed
     */
    public function isImageTransformationEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_AUTO_OPTIMISATION, $storeId);
    }

    /**
     * Get global custom transformation
     *
     * @param int $storeId
     * @return mixed
     */
    public function getGlobalCustomTransformation($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_GLOBAL_CUSTOM_TRANSFORMATION, $storeId);
    }

    /**
     * Get product custom transformation
     *
     * @param int $storeId
     * @return mixed
     */
    public function getProductCustomTransformation($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PRODUCT_CUSTOM_TRANSFORMATION, $storeId);
    }

    /**
     * Log data in logger (/var/log/pixelbin.log)
     *
     * @param string $message
     * @param array $context
     * @param string $type
     * @return void
     */
    public function logData(string $message, array $context = [], string $type = "")
    {
        switch ($type) {
            case "error":
                $this->logger->error($message, $context);
                break;
            case "critical":
                $this->logger->critical($message, $context);
                break;
            case "alert":
                $this->logger->alert($message, $context);
                break;
            default:
                $this->logger->info($message, $context);
                break;
        }
    }

    /**
     * Get media url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Check use of default image enabled
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function isDefaultImageEnabled()
    {
        return $this->getConfigValue(self::XML_PATH_SETUP_USE_DEFAULT_IMAGE);
    }

    /**
     * Get default image
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDefaultImage()
    {
        return $this->getMediaUrl() . 'pixel_bin/' . $this->getConfigValue(self::XML_PATH_SETUP_DEFAULT_IMAGE);
    }

    /**
     * Replace product image url with pixelbin url
     *
     * @param string $imageUrl
     * @return array|string|string[]
     * @throws NoSuchEntityException
     */
    public function replaceProductImageUrlWithPixelbin($imageUrl)
    {
        if ($imageUrl != null) {
            if ($this->isDefaultImageEnabled()) {
                if (strpos($imageUrl, 'Magento_Catalog/images/product/placeholder/thumbnail.jpg') !== 0 ||
                    strpos($imageUrl, 'pixel_bin') !== 0) {
                    return $this->getDefaultImage();
                }
            }

            $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);

            $imagePathArray = explode('media/', $imagePath);

            if (is_array($imagePathArray) && array_key_exists(1, $imagePathArray)) {
                $pixelbinImage = $this->getAppZone().$imagePathArray[1];
            } else {
                $pixelbinImage = $imagePath;
            }

            $storeId = $this->getStoreId();

            if ($this->isImageTransformationEnabled($storeId)) {
                $globalTransformation = $this->getGlobalCustomTransformation($storeId);
                $productTransformation = $this->getProductCustomTransformation($storeId);

                if ($globalTransformation && !$productTransformation) {
                    $transformation = '/'.$globalTransformation.'/';
                    $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
                }

                if (!$globalTransformation && $productTransformation) {
                    $transformation = '/'.$productTransformation.'/';
                    $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
                }

                if ($globalTransformation && $productTransformation) {
                    if ($globalTransformation === $productTransformation) {
                        $transformation = '/'.$globalTransformation.'/';
                        $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
                    } else {
                        $transformation = '/'.$productTransformation.'/';
                        $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
                    }
                }
            }

            //@codingStandardsIgnoreStart
            if (@getimagesize($pixelbinImage)) {
            //@codingStandardsIgnoreEnd
                $imageUrl = $pixelbinImage;
            }
        }
        return $imageUrl;
    }

    /**
     * Replace Cms Image Url With Pixelbin
     *
     * @param string $imageUrl
     * @return array|mixed|string|string[]|null
     * @throws NoSuchEntityException
     */
    public function replaceCmsImageUrlWithPixelbin($imageUrl)
    {
        if ($imageUrl != null) {

            if ($this->isDefaultImageEnabled()) {
                if (strpos($imageUrl, 'Magento_Catalog/images/product/placeholder/thumbnail.jpg') !== 0 ||
                    strpos($imageUrl, 'pixel_bin') !== 0) {
                    return $this->getDefaultImage();
                }
            }

            $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);

            $imagePathArray = explode('media/', $imagePath);

            if (is_array($imagePathArray) && array_key_exists(1, $imagePathArray)) {
                $pixelbinImage = $this->getAppZone().$imagePathArray[1];
            } else {
                $pixelbinImage = $imagePath;
            }

            $storeId = $this->getStoreId();
            if ($this->isImageTransformationEnabled($storeId)) {
                $globalTransformation = $this->getGlobalCustomTransformation($storeId);
                $transformation = '/'.$globalTransformation.'/';
                $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
            }
            //@codingStandardsIgnoreStart
            if (@getimagesize($pixelbinImage)) {
            //@codingStandardsIgnoreEnd
                $imageUrl = $pixelbinImage;
            }
        }
        return $imageUrl;
    }

    /**
     * Is Enabled Lazy load
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledLazyload($storeId = null)
    {
        return (bool) $this->getConfigValue(self::XML_PATH_LAZYLOAD_ENABLED, $storeId);
    }

    /**
     * Is Lazyload auto replace Cms Blocks
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isLazyloadAutoReplaceCmsBlocks($storeId = null)
    {
        return (bool) $this->getConfigValue(self::XML_PATH_LAZYLOAD_AUTO_REPLACE_CMS_BLOCKS, $storeId);
    }

    /**
     * Get lazy load ignore cms blocks
     *
     * @param int|null $storeId
     * @return array
     */
    public function getLazyloadIgnoredCmsBlocksArray($storeId = null)
    {
        $value = ($this->getConfigValue(self::XML_PATH_LAZYLOAD_IGNORED_CMS_BLOCKS, $storeId))
            ? (array) explode(',', $this->getConfigValue(self::XML_PATH_LAZYLOAD_IGNORED_CMS_BLOCKS, $storeId))
            : [];

        return $value;
    }

    /**
     * Get lazyload threshold
     *
     * @param int|null $storeId
     * @return int
     */
    public function getLazyloadThreshold($storeId = null)
    {
        return (int) $this->getConfigValue(self::XML_PATH_LAZYLOAD_THRESHOLD, $storeId);
    }

    /**
     * Get Lazyload effect
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLazyloadEffect($storeId = null)
    {
        return (string) $this->getConfigValue(self::XML_PATH_LAZYLOAD_EFFECT, $storeId);
    }

    /**
     * Get lazyload placeholder
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLazyloadPlaceholder($storeId = null)
    {
        return (string) $this->getConfigValue(self::XML_PATH_LAZYLOAD_PLACEHOLDER, $storeId);
    }

    /**
     * Get TotalSteps
     *
     * @param [object] $sourceModel
     * @return int
     */
    public function getTotalSteps($sourceModel)
    {
        $offset = 0;
        while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
            $offset += count($files);
        }
        return $offset;
    }


    /**
     * Parse Pixelbin URL
     * @method parsePixelbinUrl
     * @param  string             $url
     * @param  string|null        $publicId
     * @return array
     */
    public function parsePixelbinUrl($url, $publicId = null)
    {
        $parsedUrlParts = $this->mbParseUrl($url);
        $url = preg_replace('/\?.*/', '', $url);

        $parsed = [
            "orig_url" => $url,
            "scheme" => isset($parsedUrlParts["scheme"]) ? $parsedUrlParts["scheme"] : null,
            "host" => isset($parsedUrlParts["host"]) ? $parsedUrlParts["host"] : null,
            "path" => isset($parsedUrlParts["path"]) ? $parsedUrlParts["path"] : null,
            "query" => isset($parsedUrlParts["query"]) ? $parsedUrlParts["query"] : null,
            "extension" => \pathinfo($url, PATHINFO_EXTENSION),
            "type" => null,
            "cloudName" => null,
            "version" => null,
            "publicId" => ltrim((string) $publicId, '/') ?: null,
            "transformations_string" => null,
            "transformations" => [],
            "transformationless_url" => $url,
            "versionless_url" => $url,
            "versionless_transformationless_url" => $url,
            "thumbnail_url" => null,
        ];

        $_url = ltrim($parsed["path"], '/');
        $_url = preg_replace('/\.[^.]+$/', '', $_url);

        preg_match('/\/v[0-9]{1,10}\//', $_url, $version);
        if ($version && isset($version[0])) {
            $parsed["version"] = trim($version[0], '/');
        }

        if (!$parsed["publicId"] && $parsed["version"]) {
            $parsed["publicId"] = preg_replace('/.+\/v[0-9]{1,10}\//', '', $_url);
        }

        $_url = preg_replace('/(\/|\/v[0-9]{1,10}\/)' . \preg_quote((string) $parsed["publicId"], '/') . '$/', '', $_url);
        $_url = explode('/', $_url);

        $slug = \array_shift($_url);
        if (\in_array($slug, ["image","video"])) {
            $parsed["type"] = $slug;
        } else {
            $parsed["cloudName"] = $slug;
        }

        $slug = \array_shift($_url);
        $parsed["type"] = ($parsed["cloudName"] && $slug  === "video") ? "video" : "image";

        if (isset($parsed['extension'])) {
            $parsed['type'] = (in_array($parsed['extension'], $this->getSupportedVideoFormats())) ? 'video' : 'image';
        }

        $slug = \array_shift($_url);
        $parsed["transformations_string"] = ($slug === 'upload' ? '' : $slug) . implode('/', $_url);

        if ($parsed["transformations_string"]) {
            $parsed["transformations"] = explode(',', \str_replace('/', ',', $parsed["transformations_string"]));
            $parsed["transformationless_url"] = preg_replace('/\/' . \preg_quote($parsed["transformations_string"], '/') . '\//', '/', $url, 1);
        }

        $parsed["versionless_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $url, 1);
        $parsed["versionless_transformationless_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $parsed["transformationless_url"], 1);

        if ($parsed["type"] === "video") {
            $parsed["thumbnail_url"] = preg_replace('/\.[^.]+$/', '', $url);
            $parsed["thumbnail_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $parsed["thumbnail_url"]);
            $parsed["thumbnail_url"] = preg_replace('/\/(' . \preg_quote((string) $parsed["publicId"], '/') . ')$/', '/so_auto/$1.jpg', $parsed["thumbnail_url"]);
        }
        return $parsed;
    }

    /**
     * UTF-8 aware parse_url() replacement.
     *
     * @return array
     */
    public function mbParseUrl($url, $component = -1)
    {
        $enc_url = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $url
        );
        $parts = parse_url($enc_url, $component);
        if ($parts === false) {
            throw new \InvalidArgumentException('Malformed URL: ' . $url);
        }
        if (is_array($parts)) {
            foreach ($parts as $name => $value) {
                $parts[$name] = rawurldecode($value);
            }
        } else {
            $parts = rawurldecode($parts);
        }
        return $parts;
    }

    /**
     * @return array
     */
    public function getSupportedVideoFormats()
    {
        return ['mp4', 'webm', 'ogv', 'mov', 'wmv'];
    }

    /**
     * Check if the file is a vector image
     *
     * @param $file
     * @return bool
     */
    public function isVectorImage($file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (empty($extension) && file_exists($file)) {
            $mimeType = mime_content_type($file);
            $extension = str_replace('image/', '', $mimeType);
        }

        return in_array($extension, $this->getVectorExtensions());
    }

    /**
     * Get vector image extensions
     *
     * @return array
     */
    public function getVectorExtensions()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VECTOR_EXTENSIONS, 'store') ?: [];
    }

    /**
     * Get web image extensions
     *
     * @return array
     */
    public function getWebImageExtensions()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEB_IMAGE_EXTENSIONS, 'store') ?: [];
    }
}
