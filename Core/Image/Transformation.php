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

namespace Pixelbinio\Pixelbin\Core\Image;

use Pixelbinio\Pixelbin\Core\Image\Transformation\Crop;
use Pixelbinio\Pixelbin\Core\Image\Transformation\DefaultImage;
use Pixelbinio\Pixelbin\Core\Image\Transformation\Dimensions;
use Pixelbinio\Pixelbin\Core\Image\Transformation\Dpr;
use Pixelbinio\Pixelbin\Core\Image\Transformation\FetchFormat;
use Pixelbinio\Pixelbin\Core\Image\Transformation\Freeform;
use Pixelbinio\Pixelbin\Core\Image\Transformation\Gravity;
use Pixelbinio\Pixelbin\Core\Image\Transformation\Quality;

class Transformation
{
    /**
     * @var defaultImage
     */
    private $defaultImage;

    /**
     * @var gravity
     */
    private $gravity;

    /**
     * @var dimensions
     */
    private $dimensions;

    /**
     * @var crop
     */
    private $crop;

    /**
     * @var fetchFormat
     */
    private $fetchFormat;

    /**
     * @var quality
     */
    private $quality;

    /**
     * @var dpr
     */
    private $dpr;

    /**
     * @var flags
     */
    private $flags;

    /**
     * @var freeform
     */
    private $freeform;

    /**
     * Default Values
     */
    public function __construct()
    {
        $this->crop = 'lpad';
        $this->flags = [];
    }

    /**
     * Get default image path
     *
     * @param DefaultImage $defaultImage
     * @return $this
     */
    public function withDefaultImage(DefaultImage $defaultImage)
    {
        $this->defaultImage = trim((string)$defaultImage);
        return $this;
    }

    /**
     * Get crop type value
     *
     * @param Gravity $gravity
     * @return $this
     */
    public function withGravity(Gravity $gravity)
    {
        $this->gravity = $gravity;
        $this->crop = ((string)$gravity) ? 'crop' : 'lpad';
        return $this;
    }

    /**
     * Get image dimensions
     *
     * @param Dimensions $dimensions
     * @return $this
     */
    public function withDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * Get if image is croppped
     *
     * @param Crop $crop
     * @return $this
     */
    public function withCrop(Crop $crop)
    {
        $this->crop = $crop;
        return $this;
    }

    /**
     * Get image format
     *
     * @param FetchFormat $fetchFormat
     * @return $this
     */
    public function withFetchFormat(FetchFormat $fetchFormat)
    {
        $this->fetchFormat = $fetchFormat;
        return $this;
    }

    /**
     * Get image quality
     *
     * @param Quality $quality
     * @return $this
     */
    public function withQuality(Quality $quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Get image dpr
     *
     * @param Dpr $dpr
     * @return $this
     */
    public function withDpr(Dpr $dpr)
    {
        $this->dpr = $dpr;
        return $this;
    }

    /**
     * Get image freeform
     *
     * @param Freeform $freeform
     * @param bool|string $append
     * @return $this
     */
    public function withFreeform(Freeform $freeform, $append = true)
    {
        $this->freeform = trim(($append) ? $this->freeform . "," . $freeform : $freeform, ",");
        return $this;
    }

    /**
     * Add Flags to value
     *
     * @param array $flags
     * @return $this
     */
    public function addFlags(array $flags = [])
    {
        $this->flags += $flags;
        return $this;
    }

    /**
     * Builder for Transformation
     *
     * @return Transformation
     * @codingStandardsIgnoreStart
     */
    public static function builder()
    {
        return new Transformation();
    }

    /**
     * Build transform data
     *
     * @return array
     */
    public function build()
    {
        return [
            ['raw_transformation' => (string)$this->freeform],
            [
                'fetch_format' => (string)$this->fetchFormat,
                'quality' => (string)$this->quality ?: null,
                'crop' => (string)$this->crop,
                'gravity' => (string)$this->gravity ?: null,
                'width' => $this->dimensions ? $this->dimensions->getWidth() : null,
                'height' => $this->dimensions ? $this->dimensions->getHeight() : null,
                'dpr' => (string)$this->dpr,
                'flags' => $this->flags,
                'default_image' => $this->defaultImage,
            ]
        ];
    }
}
