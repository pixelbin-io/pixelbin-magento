<?php

namespace Pixelbinio\Pixelbin\Controller\Adminhtml\Ajax;

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

class UpdateAdminImage extends Action
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
    private $_authorised;

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
