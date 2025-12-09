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

namespace Pixelbinio\Pixelbin\Model\Repository;

use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\EncoderInterface;
use Pixelbin\Utils\Url;

class ResourcesManagement implements \Pixelbinio\Pixelbin\Api\ResourcesManagementInterface
{
    /**
     * @var Bool
     */
    private $initialized;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $maxResults;

    /**
     * @var string
     */
    protected $resourceType = "image";

    /**
     * @var array
     */
    protected $resourceData = [];

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

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
        $this->helperData = $helperData;
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        $this->request = $request;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Initialize
     *
     * @return $this|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initialize()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            if (($id = $this->request->getParam("id"))) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $this->setId(\rawurldecode($id));
            }
            if (($maxResults = $this->request->getParam("max_results"))) {
                $this->setMaxResults($maxResults);
            }
            if ($this->helperData->isModuleEnabled()) {
                $this->uploadFileToPixelbin->getPixelbinObj();
                return Url::url_to_obj($this->id);
            }
        }
        return $this;
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setting the max results
     *
     * @param int $maxResults
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    /**
     * Get Max Result
     *
     * @return mixed
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Get details of a single resource
     *
     * @method getResourceData
     * @return string (json encoded data)
     */
    protected function getResourceData()
    {
        try {
            $response = $this->initialize();
            return $this->jsonEncoder->encode(
                [
                    "error" => 0,
                    "data" => $response
                ]
            );
        } catch (\Exception $e) {
            return $this->jsonEncoder->encode(
                [
                    "error" => 1,
                    "message" => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        $this->resourceType = "image";
        return $this->getResourceData();
    }

    /**
     * @inheritdoc
     */
    public function getVideo()
    {
        $this->resourceType = "video";
        return $this->getResourceData();
    }

    /**
     * @inheritdoc
     */
    public function getResourcesByTag()
    {
        $response = [
            "error" => 1,
            "message" => ""
        ];
        try {
            $this->initialize();
            $resources = $this->_api->assetsByTag(
                $this->getId(),
                [
                    "resource_type" => $this->resourceType,
                    "max_results" => (int) $this->maxResults || null
                ]
            )['resources'];
            $response["error"] = 0;
            $response["data"] = $resources;
        } catch (\Exception $e) {
            $response["message"] = $e->getMessage();
        }
        return $this->jsonEncoder->encode($response);
    }
}
