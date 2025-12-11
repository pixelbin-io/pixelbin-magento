<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Plugin;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Template\Filter;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Asset\Repository;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

abstract class AbstractPixelbinPlugin
{
    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Images
     */
    protected $wysiwygImages;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * DirectivePlugin constructor.
     *
     * @param DecoderInterface $urlDecoder
     * @param Filter $filter
     * @param RawFactory $resultRawFactory
     * @param HelperData $helperData
     * @param Images $wysiwygImages
     * @param Repository $assetRepo
     */
    public function __construct(
        DecoderInterface $urlDecoder,
        Filter $filter,
        RawFactory $resultRawFactory,
        HelperData $helperData,
        Images $wysiwygImages,
        Repository $assetRepo
    ) {
        $this->urlDecoder = $urlDecoder;
        $this->filter = $filter;
        $this->resultRawFactory = $resultRawFactory;
        $this->helperData = $helperData;
        $this->wysiwygImages = $wysiwygImages;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Save SVG vector image
     *
     * @param string $imagePath
     * @return Raw
     * @throws LocalizedException
     */
    public function saveSvgVectorImage($imagePath)
    {
        if (!$this->helperData->isVectorImage($imagePath)) {
            throw new LocalizedException(__('This is not a vector image'));
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader('Content-Type', 'image/svg+xml');
        //@codingStandardsIgnoreStart
        $resultRaw->setContents(file_get_contents($imagePath));
        //@codingStandardsIgnoreEnd
        return $resultRaw;
    }
}
