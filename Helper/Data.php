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
    )
    {
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
     * Get API host domain
     *
     * @param $storeId
     * @return string
     */
    public function getApiHostDomain($storeId = null)
    {
        $host = '';
        return ltrim($host, "https://");
    }

    /**
     * @param $path
     * @param $method
     * @param $appToken
     * @param $params
     * @param $body
     * @return array
     */
    public function getSignature($path, $method, $appToken, $params, $body)
    {
        $host = $this->getApiHostDomain();
        $headers = [
            ['Authorization' => "Bearer " . $appToken]
        ];

        $exclude_headers = [
            'authorization' => true,
            'connection' => true,
            'x-amzn-trace-id' => true,
            'user-agent' => true,
            'expect' => true,
            'presigned-expires' => true,
            'range' => true
        ];

        $sign_query = false;

        $fp_date = date('Ymd\This\Z');

        $headers['x-ebg-date'] = $fp_date;

        $kCredentials = "1234567"; //This is static key
        $bodyHash = hash('sha256', $body);

        //Generate canonical headers. Remember we want /\n after last parameter as well
        $canheaders = [
            'host:' . $host,
            'x-ebg-date:' . $fp_date
        ];
        $canheadersnew = '';
        foreach ($canheaders as $key => $value) {
            $canheadersnew .= $value . "\n";
        }

        // Generate canonical request. We don't want /\n after last parameter
        $canonicalReq = [
            $method,
            $path,
            $params,
            $canheadersnew,
            'host;x-ebg-date',
            $bodyHash,
        ];
        $canonicalReqnew = '';
        foreach ($canonicalReq as $key => $value) {
            $canonicalReqnew .= $value . "\n";
        }
        $canonicalReqnew = trim($canonicalReqnew);

        //encode canonical request & add fp date parameter to one array. later on convert this array to string & remove \n after last element
        $strTosign = [
            date('Ymd\This\Z'),
            hash('sha256', $canonicalReqnew),
        ];

        $strTosignnew = '';
        foreach ($strTosign as $key => $value) {
            $strTosignnew .= $value . "\n";
        }
        $strTosignnew = trim($strTosignnew);

        // Final signature generation
        $signature = 'v1:' . hash_hmac('sha256', $strTosignnew, $kCredentials);

        $result['ebg-date'] = $fp_date;
        $result['ebg-signature'] = $signature;

        return $result;
    }

    /**
     * @return void
     */
    public function uploadFileToPixelbin()
    {
        $appToken = $this->getAppApiSecret($storeId);
        $host = $this->getApiHostDomain();
        $path = "/service/platform/assets/v1.0/upload/direct";
        $method = 'POST';

        $body = '';
        $params = '';
        $apiUrl = $host . $path . '?' . $params;

        $this->logger->info("Api URL: " . $apiUrl);

        $signData = $this->getSignature($path, $method, $appToken, $params, $body);

        $authorization = "Bearer " . $appToken;

        $this->curl->addHeader("Authorization", $authorization);
        $this->curl->addHeader("x-ebg-date", $signData['fp-date']);
        $this->curl->addHeader("x-ebg-signature", $signData['fp-signature']);
        $this->curl->setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        $this->curl->post($apiUrl, $body);
        $response = $this->curl->getBody();

        $this->logger->info("Api URL response: " . json_encode($response));
    }
}
