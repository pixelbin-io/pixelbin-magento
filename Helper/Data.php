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
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Exception\NoSuchEntityException;
use Pixelbinio\Pixelbin\Api\Data\PixelbinSynchronisationInterface;
use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncStatus;
use Pixelbinio\Pixelbin\Model\ResourceModel\PixelbinSynchronisation\CollectionFactory as PixelbinSyncCollectionFactory;

class Data extends AbstractHelper
{
    /* Get system config fields */
    public const XML_PATH_EXTENSION_ENABLE = 'pixelbin/general/is_enable';
    public const XML_PATH_APP_CLOUD_NAME = 'pixelbin/app_configuration/cloud_name';
    public const XML_PATH_APP_ZONE = 'pixelbin/app_configuration/zone';
    public const XML_PATH_APP_API_SECRET = 'pixelbin/app_configuration/api_secret';
    public const XML_PATH_AUTO_OPTIMISATION = 'pixelbin/image_transformations/auto_optimisation';
    public const XML_PATH_GLOBAL_CUSTOM_TRANSFORMATION = 'pixelbin/image_transformations/global_custom_transformation';
    //@codingStandardsIgnoreStart
    public const XML_PATH_PRODUCT_CUSTOM_TRANSFORMATION = 'pixelbin/image_transformations/product_custom_transformation';
    //@codingStandardsIgnoreEnd
    public const XML_PATH_MANUAL_CRON_ENABLED = 'pixelbin/pixelbin_image_sync/enable_manual_sync_cron';
    public const XML_PATH_VECTOR_EXTENSIONS = 'pixelbin/extensions/vector';
    public const XML_PATH_WEB_IMAGE_EXTENSIONS = 'pixelbin/extensions/web_image';
    public const API_URL = "https://api.pixelbin.io";
    public const ZONE_DEFAULT_URL = "https://cdn.pixelbin.io/v2/";
    public const EXCLUDE_FOLDERS = [
        ".thumbscatalog",
        ".thumbswysiwyg",
        "catalog/tmp/",
        "catalog/product/cache/",
        "tmp",
        "_MACOSX",
    ];
    public const EXCLUDE_EXTENSION = [
        "css",
        "js",
        "htaccess",
        "json",
        "txt",
        "csv",
        "zip",
        "tar",
        "tar.gz",
        "gz",
        "sql",
        "DS_Store",
    ];
    public const ALLOWED_EXTENSION_SYNC = [
        "png",
        "jpg",
        "jpeg",
        "webp",
        "svg",
        "gif",
        "tiff",
        "tif",
        "avif",
        "raw"
    ];

    public const ALLOWED_EXTENSION_FOR_TRANSFORMATION = ['png', 'jpeg', 'jpg', 'webp', 'tif', 'tiff', 'avif', 'raw'];

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
    //@codingStandardsIgnoreStart
    public const PIXELBIN_DEFAULT_IMAGE_URL = "https://cdn.pixelbin.io/v2/dummy-cloudname/original/magento_icons_and_images/pixelbin_logo.png";
    //@codingStandardsIgnoreEnd

    // @codingStandardsIgnoreLine
    public const TRANSFORMATION_REGEX = '/^[a-zA-Z]\w*\.[a-zA-Z]\w*\((?:\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?)(?:,\s*\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?))*)?\)(?:~[a-zA-Z]\w*\.[a-zA-Z]\w*\((?:\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?)(?:,\s*\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?))*)?\))*$/';

    /**
     * @var null
     */
    protected $appZoneLink = null;

    /**
     * @var null
     */
    protected $syncStatus = null;

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
     * @var PixelbinSyncCollectionFactory
     */
    protected $pixelbinSyncCollectionFactory;

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
     * @param PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
     */
    public function __construct(
        Context $context,
        Curl $curl,
        CurlFactory $curlFactory,
        JsonHelperData $jsonHelper,
        Logger $logger,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        PixelbinSyncCollectionFactory $pixelbinSyncCollectionFactory
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->curlFactory = $curlFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->pixelbinSyncCollectionFactory = $pixelbinSyncCollectionFactory;
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
        if ($this->appZoneLink === null) {
            $zoneSlug = $this->getConfigValue(self::XML_PATH_APP_ZONE, $storeId);
            if (!empty($zoneSlug)) {
                $this->appZoneLink = self::ZONE_DEFAULT_URL . $this->getAppCloudName() . "/" . $zoneSlug . "original/";
            }
            $this->appZoneLink = self::ZONE_DEFAULT_URL . $this->getAppCloudName() . "/original/";
        }
        return $this->appZoneLink;
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
        $log_enabled = $this->scopeConfig->getValue('pixelbin/developer/enabled_log');
        if ($log_enabled) {
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
     * Replace product image url with pixelbin url
     *
     * @param string $imageUrl
     * @return array|string|string[]
     * @throws NoSuchEntityException
     */
    public function replaceProductImageUrlWithPixelbin($imageUrl)
    {
        return $this->replaceImageUrlWithPixelbin($imageUrl, true);
    }

    /**
     * Common function to replace image URL with Pixelbin transformation
     *
     * @param string $imageUrl
     * @param bool $isProduct
     * @param bool $isGraphql
     * @return string
     * @throws NoSuchEntityException
     */
    private function replaceImageUrlWithPixelbin($imageUrl, $isProduct = false, $isGraphql = false)
    {
        if (empty($imageUrl)) {
            return $imageUrl;
        }

        $imagePath = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);
        $imagePathArray = explode('media/', $imagePath);
        $pixelbinImage = (is_array($imagePathArray) && isset($imagePathArray[1]))
            ? $this->getAppZone() . $imagePathArray[1]
            : $imagePath;

        $storeId = $this->getStoreId();
        $allowedFormats = self::ALLOWED_EXTENSION_FOR_TRANSFORMATION;

        // @codingStandardsIgnoreLine
        $extension = strtolower(pathinfo($imageUrl, PATHINFO_EXTENSION));
        if ($this->isImageTransformationEnabled($storeId) && in_array($extension, $allowedFormats)) {
            $globalTransformation = $this->getGlobalCustomTransformation($storeId);
            $productTransformation = $this->getProductCustomTransformation($storeId);

            // For CMS images (no product transformation)
            if (!$isProduct && $globalTransformation) {
                $transformation = '/' . $globalTransformation . '/';
                $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
            }

            // For Product Images
            if ($isProduct) {
                if ($globalTransformation && !$productTransformation) {
                    $transformation = '/' . $globalTransformation . '/';
                } elseif (!$globalTransformation && $productTransformation) {
                    $transformation = '/' . $productTransformation . '/';
                } else {
                    $transformation = '/' . ($productTransformation ?: $globalTransformation) . '/';
                }
                $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
            }
        }

        if ($this->checkSyncStatus() || $isGraphql) {
            return $pixelbinImage;
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
        return $this->replaceImageUrlWithPixelbin($imageUrl, false);
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
     * Supported video formats
     *
     * @return array
     */
    public function getSupportedVideoFormats()
    {
        return ['mp4', 'webm', 'ogv', 'mov', 'wmv'];
    }

    /**
     * Check if the file is a vector image
     *
     * @param string $file
     * @return bool
     */
    public function isVectorImage($file)
    {
        return $this->getExtensionForWebpAndVector($file, $this->getVectorExtensions());
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
     * Check if the file is a vector image
     *
     * @param string $file
     * @return bool
     */
    public function isWebImage($file)
    {
        return $this->getExtensionForWebpAndVector($file, $this->getWebImageExtensions());
    }

    /**
     * Get Extension for webp and vector
     *
     * @param string $file
     * @param array $extensions
     * @return bool
     */
    public function getExtensionForWebpAndVector($file, $extensions)
    {
        //@codingStandardsIgnoreStart
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (empty($extension) && file_exists($file)) {
            $mimeType = mime_content_type($file);
            $extension = str_replace('image/', '', $mimeType);
        }
        return in_array($extension, $extensions);
        //@codingStandardsIgnoreEnd
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

    /**
     * Replace GraphQl product image url with pixelbin url
     *
     * @param string $imageUrl
     * @return array|string|string[]
     * @throws NoSuchEntityException
     */
    public function replaceGraphqlProductImageUrlWithPixelbin($imageUrl)
    {
        return $this->replaceImageUrlWithPixelbin($imageUrl, true, true);
    }

    /**
     * Replace GraphQl Cms Image Url With Pixelbin
     *
     * @param string $imageUrl
     * @return array|mixed|string|string[]|null
     * @throws NoSuchEntityException
     */
    public function replaceGraphqlCmsImageUrlWithPixelbin($imageUrl)
    {
        return $this->replaceImageUrlWithPixelbin($imageUrl, false, true);
    }

    /**
     * Validate pixelbin url
     *
     * @param string $pixelbinUrl
     * @param bool $flag
     * @return bool
     */
    public function validatePixelbinUrl($pixelbinUrl, $flag = false)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $modifyUrl1 = parse_url(self::ZONE_DEFAULT_URL);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $modifyUrl2 = parse_url($pixelbinUrl);
        if ($modifyUrl1['host'] == $modifyUrl2['host']) {
            try {
                $this->curl->setTimeout(10);
                $this->curl->get($pixelbinUrl);
                $statusCode = $this->curl->getStatus();
                $body = $this->curl->getBody();
                $this->logData("image url request => " . $pixelbinUrl);
                $this->logData("image url response => " . $body);
                if ($this->isJson($body)) {
                    $data = json_decode($body, true);
                    if ($flag) {
                        return $data;
                    }
                    $status = $data["status"] ?? "";
                    if ($status != 200) {
                        return false;
                    }
                }
            } catch (\Exception $e) {
                $this->logData("image url request => " . $pixelbinUrl);
                $this->logData('Pixelbin image API Error Exception => : ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Is json valid
     *
     * @param string $string
     * @return bool
     */
    public function isJson(string $string): bool
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Check sync status
     *
     * @return bool|null
     */
    public function checkSyncStatus()
    {
        if ($this->syncStatus === null) {
            $this->syncStatus = false;
            $totalCollection = $this->pixelbinSyncCollectionFactory->create()->getSize();
            $pendingToSync = $this->pixelbinSyncCollectionFactory->create()
                ->addFieldToFilter(
                    PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                    SyncStatus::STATUS_PENDING
                )->getSize();
            $pendingToStart = $this->pixelbinSyncCollectionFactory->create()
                ->addFieldToFilter(
                    PixelbinSynchronisationInterface::KEY_SYNC_STATUS,
                    SyncStatus::STATUS_PENDING_TO_START
                )->getSize();
            if ($totalCollection > 0 && $pendingToSync == 0 && $pendingToStart == 0) {
                $this->syncStatus = true;
            }
        }
        return $this->syncStatus;
    }
}
