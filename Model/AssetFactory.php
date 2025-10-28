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
 * @version     1.0.1
 */

namespace Pixelbinio\Pixelbin\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class AssetFactory extends AssetInterfaceFactory
{
    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * AssetFactory constructor.
     *
     * @param AssetInterfaceFactory $assetFactory
     * @param Filesystem $filesystem
     * @param HelperData $helperData
     */
    public function __construct(
        AssetInterfaceFactory $assetFactory,
        Filesystem $filesystem,
        HelperData $helperData
    ) {
        $this->assetFactory = $assetFactory;
        $this->filesystem = $filesystem;
        $this->helperData = $helperData;
    }

    /**
     * Set height and width for SVG images when saving to DB
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        if ((empty($data['width']) || empty($data['height']))
            && isset($data['path'])
            && $this->helperData->isVectorImage($data['path'])
        ) {
            $absolutePath = $this->getMediaDirectory()->getAbsolutePath($data['path']);
            $width = 300;
            $height = 150;

            $svg = simplexml_load_file($absolutePath);
            if (!empty($svg['width']) && !empty($svg['height'])) {
                $width = (int)$svg['width'];
                $height = (int)$svg['height'];
            }

            $data['width'] = $width;
            $data['height'] = $height;
        }

        return $this->assetFactory->create($data);
    }

    /**
     * Retrieve media directory instance with read access
     *
     * @return ReadInterface
     */
    private function getMediaDirectory()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
