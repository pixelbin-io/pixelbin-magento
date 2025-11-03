<?php

namespace Pixelbinio\Pixelbin\Core;

class Image implements ImageInterface
{
    /**
     * @var string
     */
    private $imagePath;

    /**
     * @var string
     */
    private $relativePath;

    /**
     * @var array
     */
    private $pathInfo;

    /**
     * @param string $imagePath
     * @param string $relativePath
     */
    private function __construct($imagePath, $relativePath = '')
    {
        $this->imagePath = $imagePath;
        $this->relativePath = $relativePath;
        //@codingStandardsIgnoreStart
        $this->pathInfo = pathinfo($this->imagePath);
        //@codingStandardsIgnoreEnd
    }

    /**
     * Retruns Image path
     *
     * @param string $imagePath
     * @param string $relativePath
     * @return Image
     */
    public function fromPath($imagePath, $relativePath = '')
    {
        return new Image($imagePath, $relativePath);
    }

    /**
     * Returns a String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->imagePath;
    }

    /**
     * Get Relative Path
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Get Relative Folder
     *
     * @return string
     */
    public function getRelativeFolder()
    {
        //@codingStandardsIgnoreStart
        $result = dirname($this->getRelativePath());
        //@codingStandardsIgnoreEnd
        return $result == '.' ? '' : $result;
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getId()
    {
        return sprintf(
            '%s%s',
            $this->relativePath ? ($this->getRelativeFolder() . DIRECTORY_SEPARATOR) : '',
            $this->pathInfo['basename']
        );
    }

    /**
     * Get Id Without Extension
     *
     * @return string
     */
    public function getIdWithoutExtension()
    {
        return sprintf(
            '%s%s',
            $this->relativePath ? ($this->getRelativeFolder() . DIRECTORY_SEPARATOR) : '',
            $this->pathInfo['filename']
        );
    }

    /**
     * Get Extension
     *
     * @return mixed
     */
    public function getExtension()
    {
        return $this->pathInfo['extension'];
    }
}
