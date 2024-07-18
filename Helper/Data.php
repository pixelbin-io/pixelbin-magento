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
    const XML_PATH_EXTENSION_ENABLE = 'pixelbin/general/is_enable';

    const XML_PATH_APP_CLOUD_NAME = 'pixelbin/app_configuration/cloud_name';
    const XML_PATH_APP_API_URL = 'pixelbin/app_configuration/api_url';
    const XML_PATH_APP_ZONE = 'pixelbin/app_configuration/zone';
    const XML_PATH_APP_API_SECRET = 'pixelbin/app_configuration/api_secret';

    const XML_PATH_SETUP_DEFAULT_IMAGE = 'pixelbin/pixelbin_setup/default_image';

    const XML_PATH_AUTO_OPTIMISATION = 'pixelbin/image_transformations/auto_optimisation';
    const XML_PATH_PRODUCT_CUSTOM_TRANSFORMATION = 'pixelbin/image_transformations/product_custom_transformation';

    const API_VERSION = "v2";

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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
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
     * Get Pixelbin API url
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->getConfigValue(self::XML_PATH_APP_API_URL);
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
        return $this->getConfigValue(self::XML_PATH_APP_ZONE, $storeId);
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

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getDefaultImage()
    {
        return $this->getMediaUrl() . 'pixel_bin/' . $this->getConfigValue(self::XML_PATH_SETUP_DEFAULT_IMAGE);
    }

    public function replaceProductImageUrlWithPixelbin($imageUrl)
    {
        $pixelbinImage = preg_replace('/\/cache\/[a-f0-9]{32}\//', '/', $imageUrl);
        
        $storeId = $this->getStoreId();
        
        if ($this->isImageTransformationEnabled($storeId)) {
            $productTransformation = $this->getProductCustomTransformation($storeId);        
            $transformation = '/'.$productTransformation.'/';
            $pixelbinImage = preg_replace('/\/original\//', "$transformation", $pixelbinImage);
        }

        if (isset($pixelbinImage)) {
            $result = $pixelbinImage;
        } elseif(strpos('Magento_Catalog/images/product/placeholder/thumbnail.jpg', $imageUrl) > 0) {
            $result = $this->getDefaultImage();
        } else {
            $result = $this->getDefaultImage();
        }

        return $result;
    }
}
