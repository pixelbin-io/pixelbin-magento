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

namespace Pixelbinio\Pixelbin\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Logger\Logger;

/**
 * Cms page data provider
 */
class Page
{
    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageByIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param HelperData $helperData
     * @param Logger $logger
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier,
        HelperData $helperData,
        Logger $logger
    ) {

        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * Returns page data by page_id
     *
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageId(int $pageId): array
    {
        $page = $this->pageRepository->getById($pageId);

        return $this->convertPageData($page);
    }

    /**
     * Returns page data by page identifier
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageIdentifier(string $pageIdentifier, int $storeId): array
    {
        $page = $this->pageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page);
    }

    /**
     * Convert page data
     *
     * @param PageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    private function convertPageData(PageInterface $page)
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->replaceImageUrl($this->widgetFilter->filter($page->getContent()));

        $pageData = [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
        ];
        return $pageData;
    }

    /**
     * Replace image url
     *
     * @param string $html
     * @return array|mixed|string|string[]
     * @throws NoSuchEntityException
     */
    public function replaceImageUrl($html)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return $html;
        }
        if (stripos($html, "&lt;img ") !== false) {

//            $dom = new \domDocument();
//            $useErrors = libxml_use_internal_errors(true);
//            $dom->loadHTML($html);
//            libxml_use_internal_errors($useErrors);
//            $dom->preserveWhiteSpace = false;
//            $modified = 0;

            preg_match_all('/img[^>]+g"/i', $html, $images);
            foreach ($images[0] as $image) {
                $secureImg = str_replace('img src="', '', $image);
                $secureImg = str_replace('"', '', $secureImg);

                $secureImg = $this->helperData->replaceGraphqlCmsImageUrlWithPixelbin($secureImg);

                $secureImg = '&lt;img src="'. $secureImg .'"';

                $html = str_replace($image, $secureImg, $html);
            }
        }
        return $html;
    }
}
