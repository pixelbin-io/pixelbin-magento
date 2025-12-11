<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Ajax;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\Filesystem as FileSysten;
use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Pixelbinio\Pixelbin\Core\Image\Transformation;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class UpdateAdminImage extends Action implements CsrfAwareActionInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ResultRawFactory
     */
    protected $resultFactory;

    /**
     * @var FileSysten
     */
    protected $filesystem;

    /**
     * @var string
     */
    private $authorised;

    /**
     * @var Transformation
     */
    protected $transformation;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param ResultRawFactory $resultFactory
     * @param FileSysten $filesystem
     * @param Transformation $transformation
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        ResultRawFactory $resultFactory,
        FileSysten $filesystem,
        Transformation $transformation,
        HelperData $helperData
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->resultFactory = $resultFactory;
        $this->filesystem = $filesystem;
        $this->transformation = $transformation;
        $this->helperData = $helperData;
    }

    /**
     * Create CSRF validation exception
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate for CSRF
     *
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Update Admin Image Execute
     *
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $result = [];
        $response = $this->resultFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
