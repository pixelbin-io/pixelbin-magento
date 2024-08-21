<?php

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
    private $defaultImage;
    private $gravity;
    private $dimensions;
    private $crop;
    private $fetchFormat;
    private $quality;
    private $dpr;
    private $flags;
    private $freeform;

    public function __construct()
    {
        $this->crop = 'lpad';
        $this->flags = [];
    }

    public function withDefaultImage(DefaultImage $defaultImage)
    {
        $this->defaultImage = trim((string)$defaultImage);
        return $this;
    }

    public function withGravity(Gravity $gravity)
    {
        $this->gravity = $gravity;
        $this->crop = ((string)$gravity) ? 'crop' : 'lpad';
        return $this;
    }

    public function withDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function withCrop(Crop $crop)
    {
        $this->crop = $crop;
        return $this;
    }

    public function withFetchFormat(FetchFormat $fetchFormat)
    {
        $this->fetchFormat = $fetchFormat;
        return $this;
    }

    public function withQuality(Quality $quality)
    {
        $this->quality = $quality;
        return $this;
    }

    public function withDpr(Dpr $dpr)
    {
        $this->dpr = $dpr;
        return $this;
    }

    public function withFreeform(Freeform $freeform, $append = true)
    {
        $this->freeform = trim(($append) ? $this->freeform . "," . $freeform : $freeform, ",");
        return $this;
    }

    public function addFlags(array $flags = [])
    {
        $this->flags += $flags;
        return $this;
    }

    public static function builder()
    {
        return new Transformation();
    }

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
