<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Model\File;

class UploaderFactory extends \Magento\Framework\File\UploaderFactory
{
    /**
     * Object manager class
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);

        $this->objectManager = $objectManager;
    }

    /**
     * Create new uploader instance
     *
     * @param array $data
     * @return Uploader
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(Uploader::class, $data);
    }
}
