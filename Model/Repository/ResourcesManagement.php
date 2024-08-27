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

namespace Pixelbinio\Pixelbin\Model\Repository;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\EncoderInterface;
use Pixelbin\Utils\Url;

class ResourcesManagement implements \Pixelbinio\Pixelbin\Api\ResourcesManagementInterface
{
    private $initialized;
    private $id;
    private $maxResults;
    protected $_resourceType = "image";
    protected $_resourceData = [];

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var UploadFileToPixelbin
     */
    protected $_uploadFileToPixelbin;

    /**
     * @var Http
     */
    private $_request;

    /**
     * @var EncoderInterface
     */
    private $_jsonEncoder;

    /**
     * @param HelperData $helperData
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     * @param Http $request
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(
        HelperData $helperData,
        UploadFileToPixelbin $uploadFileToPixelbin,
        Http $request,
        EncoderInterface $jsonEncoder
    ) {
        $this->_helperData = $helperData;
        $this->_uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->_request = $request;
        $this->_jsonEncoder = $jsonEncoder;
    }

    /**
     * @return $this|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initialize()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            if (($id = $this->_request->getParam("id"))) {
                $this->setId(\rawurldecode($id));
            }
            if (($maxResults = $this->_request->getParam("max_results"))) {
                $this->setMaxResults($maxResults);
            }
            if ($this->_helperData->isModuleEnabled()) {
                $this->_uploadFileToPixelbin->getPixelbinObj();
                return Url::url_to_obj($this->id);
            }
        }
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Get details of a single resource
     *
     * @method _getResourceData
     * @return string (json encoded data)
     */
    protected function _getResourceData()
    {
        try {
            $response = $this->initialize();
            return $this->_jsonEncoder->encode(
                [
                    "error" => 0,
                    "data" => $response
                ]
            );
        } catch (\Exception $e) {
            return $this->_jsonEncoder->encode(
                [
                    "error" => 1,
                    "message" => $e->getMessage()
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        $this->_resourceType = "image";
        return $this->_getResourceData();
    }

    /**
     * {@inheritdoc}
     */
    public function getVideo()
    {
        $this->_resourceType = "video";
        return $this->_getResourceData();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesByTag()
    {
        try {
            $this->initialize();
            $resources = $this->_api->assetsByTag(
                $this->getId(),
                [
                    "resource_type" => $this->_resourceType,
                    "max_results" => (int) $this->maxResults || null
                ]
            )['resources'];
            return $this->_jsonEncoder->encode(
                [
                    "error" => 0,
                    "data" => $resources
                ]
            );
        } catch (\Exception $e) {
            return $this->_jsonEncoder->encode(
                [
                    "error" => 1,
                    "message" => $e->getMessage()
                ]
            );
        }
    }
}
