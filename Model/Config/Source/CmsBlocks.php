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

namespace Pixelbinio\Pixelbin\Model\Config\Source;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CmsBlocks implements OptionSourceInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * CmsBlocks Construct
     *
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Get cms block options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->blockFactory->create()->getCollection()->setOrder('title', 'asc') as $block) {
            $options[] = [
                'value' => $block->getId(),
                'label' => $block->getTitle(),
            ];
        }
        return $options;
    }
}
