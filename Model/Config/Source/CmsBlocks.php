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

namespace Pixelbinio\Pixelbin\Model\Config\Source;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CmsBlocks implements OptionSourceInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

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
