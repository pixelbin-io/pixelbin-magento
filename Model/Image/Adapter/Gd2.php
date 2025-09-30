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

namespace Pixelbinio\Pixelbin\Model\Image\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\Framework\Phrase;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

/**
 * Gd2 adapter.
 *
 * !!! This class is almost a full copy of \Magento\Framework\Image\Adapter\Gd2
 * !!! There are only a few small changes made to the code. But rewriting the file was the only
 * !!! solution that we could find to workaround private methods and class members.
 * !!! All the changes will be highlighted with a 'FIX' label.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Gd2 extends AbstractAdapter
{
    /**
     * @var array
     */
    protected $_requiredExtensions = ["gd"];

    /**
     * Image output callbacks by type
     *
     * FIX: added 'IMAGETYPE_WEBP' element to the array
     *
     * @var array
     */
    private static $_callbacks = [
        IMAGETYPE_GIF => ['output' => 'imagegif', 'create' => 'imagecreatefromgif'],
        IMAGETYPE_JPEG => ['output' => 'imagejpeg', 'create' => 'imagecreatefromjpeg'],
        IMAGETYPE_PNG => ['output' => 'imagepng', 'create' => 'imagecreatefrompng'],
        IMAGETYPE_XBM => ['output' => 'imagexbm', 'create' => 'imagecreatefromxbm'],
        IMAGETYPE_WBMP => ['output' => 'imagewbmp', 'create' => 'imagecreatefromxbm'],
        IMAGETYPE_WEBP => ['output' => 'imagewebp', 'create' => 'imagecreatefromwebp'],
    ];

    /**
     * Whether image was resized or not
     *
     * @var bool
     */
    protected $_resized = false;

    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface $logger
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Psr\Log\LoggerInterface $logger,
        HelperData $helper,
        array $data = []
    ) {
        parent::__construct($filesystem, $logger, $data);

        $this->helper = $helper;
    }

    /**
     * For properties reset, e.g. mimeType caching.
     *
     * @return void
     */
    protected function _reset()
    {
        $this->_fileMimeType = null;
        $this->_fileType = null;
    }

    /**
     * Open image for processing
     *
     * @param string $filename
     * @return void
     * @throws \OverflowException|FileSystemException
     */
    public function open($filename)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if ($filename === null || !file_exists($filename)) {
            throw new FileSystemException(
                new Phrase('File "%1" does not exist.', [$filename])
            );
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!$filename || filesize($filename) === 0 || !$this->validateURLScheme($filename)) {
            throw new \InvalidArgumentException('Wrong file');
        }

        $this->_fileName = $filename;
        $this->_reset();
        $this->getMimeType();
        $this->_getFileAttributes();

        if ($this->_isMemoryLimitReached()) {
            throw new \OverflowException('Memory limit has been reached.');
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Extensions not supported by GD
        $unsupported = [
            'tiff', 'tif', 'avif','raw'
        ];

        if (in_array($ext, $unsupported, true)) {
            $this->_imageHandler = null;
            return;
        }

        $this->imageDestroy();
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $this->_imageHandler = call_user_func(
            $this->_getCallback(
                'create',
                null,
                sprintf('Unsupported image format. File: %s', $this->_fileName)
            ),
            $this->_fileName
        );
    }

    /**
     * Checks for invalid URL schema if it exists
     *
     * @param string $filename
     * @return bool
     */
    private function validateURLScheme(string $filename) : bool
    {
        $allowed_schemes = ['ftp', 'ftps', 'http', 'https'];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $url = parse_url($filename);
        if ($url && isset($url['scheme']) && !in_array($url['scheme'], $allowed_schemes)) {
            return false;
        }

        return true;
    }

    /**
     * Validate updated file
     *
     * @param string $filePath
     * @return bool
     */
    public function validateUploadFile($filePath)
    {
        /*
         * FIX: Skip validation for vector images
         */
        if ($this->helper->isVectorImage($filePath)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return file_exists($filePath);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (empty($extension)) {
            $mimeType = mime_content_type($filePath);
            $extension = str_replace('image/', '', $mimeType);
        }

        if (in_array($extension, HelperData::ALLOWED_EXTENSION_SYNC)) {
            return true;
        }
        return parent::validateUploadFile($filePath);
    }

    /**
     * Checks whether memory limit is reached.
     *
     * @return bool
     */
    protected function _isMemoryLimitReached()
    {
        $limit = $this->_convertToByte(ini_get('memory_limit'));
        $requiredMemory = $this->_getImageNeedMemorySize($this->_fileName);
        if ($limit === -1) {
            // A limit of -1 means no limit: http://www.php.net/manual/en/ini.core.php#ini.memory-limit
            return false;
        }
        return memory_get_usage(true) + $requiredMemory > $limit;
    }

    /**
     * Get image needed memory size
     *
     * @param string $file
     * @return float|int
     */
    protected function _getImageNeedMemorySize($file)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $imageInfo = getimagesize($file);
        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }
        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }

        return round(
            ($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + pow(2, 16)) * 1.65
        );
    }

    /**
     * Converts memory value (e.g. 64M, 129K) to bytes.
     *
     * Case insensitive value might be used.
     *
     * @param string $memoryValue
     * @return int
     */
    protected function _convertToByte($memoryValue)
    {
        if (stripos($memoryValue, 'G') !== false) {
            return (int)$memoryValue * pow(1024, 3);
        } elseif (stripos($memoryValue, 'M') !== false) {
            return (int)$memoryValue * 1024 * 1024;
        } elseif (stripos($memoryValue, 'K') !== false) {
            return (int)$memoryValue * 1024;
        }

        return (int)$memoryValue;
    }

    /**
     * Save image to specific path.
     *
     * If some folders of path does not exist they will be created
     *
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     * @throws \Exception  If destination path is not writable
     */
    public function save($destination = null, $newName = null)
    {
        $fileName = $this->_prepareDestination($destination, $newName);

        if (!$this->_resized) {
            // keep alpha transparency
            $isAlpha = false;
            $isTrueColor = false;
            $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha, $isTrueColor);
            if ($isAlpha) {
                if ($isTrueColor) {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $newImage = imagecreatetruecolor($this->_imageSrcWidth, $this->_imageSrcHeight);
                } else {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $newImage = imagecreate($this->_imageSrcWidth, $this->_imageSrcHeight);
                }
                $this->fillBackgroundColor($newImage);
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagecopy($newImage, $this->_imageHandler, 0, 0, 0, 0, $this->_imageSrcWidth, $this->_imageSrcHeight);
                $this->imageDestroy();
                $this->_imageHandler = $newImage;
            }
        }

        if ($this->_imageHandler === null) {
            $this->_imageHandler = false;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imageinterlace($this->_imageHandler, true);

        switch ($this->_fileType) {
            case IMAGETYPE_PNG:
                $quality = 9;   // For PNG files compression level must be from 0 (no compression) to 9.
                break;

            case IMAGETYPE_JPEG:
                $quality = $this->quality();
                break;

            default:
                $quality = null;    // No compression.
        }

        // Prepare callback method parameters
        $functionParameters = [$this->_imageHandler, $fileName];
        if ($quality) {
            $functionParameters[] = $quality;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        call_user_func_array($this->_getCallback('output'), $functionParameters);
    }

    /**
     * Render image and return its binary contents.
     *
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter::getImage
     *
     * @return string
     */
    public function getImage()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        ob_start();
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        call_user_func($this->_getCallback('output'), $this->_imageHandler);
        return ob_get_clean();
    }

    /**
     * Obtain function name, basing on image type and callback type
     *
     * @param string $callbackType
     * @param null|int $fileType
     * @param string $unsupportedText
     * @return string
     * @throws \InvalidArgumentException
     * @throws \BadFunctionCallException
     */
    private function _getCallback($callbackType, $fileType = null, $unsupportedText = 'Unsupported image format.')
    {
        if (null === $fileType) {
            $fileType = $this->_fileType;
        }
        if (empty(self::$_callbacks[$fileType])) {
            throw new \InvalidArgumentException($unsupportedText);
        }
        if (empty(self::$_callbacks[$fileType][$callbackType])) {
            throw new \BadFunctionCallException('Callback not found.');
        }
        return self::$_callbacks[$fileType][$callbackType];
    }

    /**
     * Fill image with main background color.
     *
     * Returns a color identifier.
     *
     * @param resource &$imageResourceTo
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function fillBackgroundColor(&$imageResourceTo): void
    {
        // try to keep transparency, if any
        if ($this->_keepTransparency) {
            $isAlpha = false;
            $transparentIndex = $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);

            try {
                // fill true color png with alpha transparency
                if ($isAlpha) {
                    $this->applyAlphaTransparency($imageResourceTo);

                    return;
                }

                if ($transparentIndex !== false) {
                    $this->applyTransparency($imageResourceTo, $transparentIndex);

                    return;
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Exception $e) {
                // fallback to default background color
            }
        }
        list($red, $green, $blue) = $this->_backgroundColor ?: [0, 0, 0];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $color = imagecolorallocate($imageResourceTo, $red, $green, $blue);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imagefill($imageResourceTo, 0, 0, $color)) {
            throw new \InvalidArgumentException("Failed to fill image background with color {$red} {$green} {$blue}.");
        }
    }

    /**
     * Method to apply alpha transparency for image.
     *
     * @param resource $imageResourceTo
     *
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function applyAlphaTransparency(&$imageResourceTo): void
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imagealphablending($imageResourceTo, false)) {
            throw new \InvalidArgumentException('Failed to set alpha blending for PNG image.');
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $transparentAlphaColor = imagecolorallocatealpha($imageResourceTo, 0, 0, 0, 127);

        if (false === $transparentAlphaColor) {
            throw new \InvalidArgumentException('Failed to allocate alpha transparency for PNG image.');
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imagefill($imageResourceTo, 0, 0, $transparentAlphaColor)) {
            throw new \InvalidArgumentException('Failed to fill PNG image with alpha transparency.');
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imagesavealpha($imageResourceTo, true)) {
            throw new \InvalidArgumentException('Failed to save alpha transparency into PNG image.');
        }
    }

    /**
     * Method to apply transparency for image.
     *
     * @param resource $imageResourceTo
     * @param int $transparentIndex
     *
     * @return void
     */
    private function applyTransparency(&$imageResourceTo, $transparentIndex): void
    {
        // fill image with indexed non-alpha transparency
        $transparentColor = false;

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if ($transparentIndex >= 0 && $transparentIndex <= imagecolorstotal($this->_imageHandler)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            list($red, $green, $blue) = array_values(imagecolorsforindex($this->_imageHandler, $transparentIndex));
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $transparentColor = imagecolorallocate($imageResourceTo, (int) $red, (int) $green, (int) $blue);
        }
        if (false === $transparentColor) {
            throw new \InvalidArgumentException('Failed to allocate transparent color for image.');
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imagefill($imageResourceTo, 0, 0, $transparentColor)) {
            throw new \InvalidArgumentException('Failed to fill image with transparency.');
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecolortransparent($imageResourceTo, $transparentColor);
    }

    /**
     * Gives true for a PNG with alpha, false otherwise
     *
     * @param string $fileName
     * @return boolean
     */
    public function checkAlpha($fileName)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return (ord(file_get_contents((string)$fileName, false, null, 25, 1)) & 6 & 4) == 4;
    }

    /**
     * Checks if image has alpha transparency
     *
     * @param resource $imageResource
     * @param int $fileType
     * @param bool $isAlpha
     * @param bool $isTrueColor
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    private function _getTransparency($imageResource, $fileType, &$isAlpha = false, &$isTrueColor = false)
    {
        $isAlpha = false;
        $isTrueColor = false;
        // assume that transparency is supported by gif/png only
        if (IMAGETYPE_GIF === $fileType || IMAGETYPE_PNG === $fileType) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $transparentIndex = imagecolortransparent($imageResource);
            if ($transparentIndex >= 0) {
                return $transparentIndex;
            } elseif (IMAGETYPE_PNG === $fileType) {
                // assume that truecolor PNG has transparency
                $isAlpha = $this->checkAlpha($this->_fileName);
                $isTrueColor = true;
                // -1
                return $transparentIndex;
            }
        }
        /*
         * FIX: added '|| IMAGETYPE_WEBP === $fileType' for the condition
         */
        if (IMAGETYPE_JPEG === $fileType || IMAGETYPE_WEBP === $fileType) {
            $isTrueColor = true;
        }
        return false;
    }

    /**
     * Change the image size
     *
     * @param null|int $frameWidth
     * @param null|int $frameHeight
     * @return void
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        $dims = $this->_adaptResizeValues($frameWidth, $frameHeight);

        // create new image
        $isAlpha = false;
        $isTrueColor = false;
        $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha, $isTrueColor);
        if ($isTrueColor) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $newImage = imagecreatetruecolor($dims['frame']['width'], $dims['frame']['height']);
        } else {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $newImage = imagecreate($dims['frame']['width'], $dims['frame']['height']);
        }

        if ($isAlpha) {
            $this->_saveAlpha($newImage);
        }

        // fill new image with required color
        $this->fillBackgroundColor($newImage);

        if ($this->_imageHandler) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            imagecopyresampled(
                $newImage,
                $this->_imageHandler,
                $dims['dst']['x'],
                $dims['dst']['y'],
                $dims['src']['x'],
                $dims['src']['y'],
                $dims['dst']['width'],
                $dims['dst']['height'],
                $this->_imageSrcWidth,
                $this->_imageSrcHeight
            );
        }
        $this->imageDestroy();
        $this->_imageHandler = $newImage;
        $this->refreshImageDimensions();
        $this->_resized = true;
    }

    /**
     * Rotate image on specific angle
     *
     * @param int $angle
     * @return void
     */
    public function rotate($angle)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $rotatedImage = imagerotate($this->_imageHandler, $angle, $this->imageBackgroundColor);
        $this->imageDestroy();
        $this->_imageHandler = $rotatedImage;
        $this->refreshImageDimensions();
    }

    /**
     * Add watermark to image
     *
     * @param string $imagePath
     * @param int $positionX
     * @param int $positionY
     * @param int $opacity
     * @param bool $tile
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false)
    {
        list($watermarkSrcWidth, $watermarkSrcHeight, $watermarkFileType,) = $this->_getImageOptions($imagePath);
        $this->_getFileAttributes();
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $watermark = call_user_func(
            $this->_getCallback('create', $watermarkFileType, 'Unsupported watermark image format.'),
            $imagePath
        );

        $merged = false;

        $watermark = $this->createWatermarkBasedOnPosition($watermark, $positionX, $positionY, $merged, $tile);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagedestroy($watermark);
        $this->refreshImageDimensions();
    }

    /**
     * Create watermark based on its image position.
     *
     * @param resource $watermark
     * @param int $positionX
     * @param int $positionY
     * @param bool $merged
     * @param bool $tile
     * @return false|resource
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function createWatermarkBasedOnPosition(
        $watermark,
        int $positionX,
        int $positionY,
        bool $merged,
        bool $tile
    ) {
        if ($this->getWatermarkWidth() &&
            $this->getWatermarkHeight() &&
            $this->getWatermarkPosition() != self::POSITION_STRETCH
        ) {
            $watermark = $this->createWaterMark($watermark, $this->getWatermarkWidth(), $this->getWatermarkHeight());
        }

        /**
         * Fixes issue with watermark with transparent background and an image that is not truecolor (e.g GIF).
         * blending mode is allowed for truecolor images only.
         * @see imagealphablending()
         */
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!imageistruecolor($this->_imageHandler)) {
            $newImage = $this->createTruecolorImageCopy();
            $this->imageDestroy();
            $this->_imageHandler = $newImage;
        }

        if ($this->getWatermarkPosition() == self::POSITION_TILE) {
            $tile = true;
        } elseif ($this->getWatermarkPosition() == self::POSITION_STRETCH) {
            $watermark = $this->createWaterMark($watermark, $this->_imageSrcWidth, $this->_imageSrcHeight);
        } elseif ($this->getWatermarkPosition() == self::POSITION_CENTER) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionX = (int) ($this->_imageSrcWidth / 2 - imagesx($watermark) / 2);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionY = (int) ($this->_imageSrcHeight / 2 - imagesy($watermark) / 2);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_RIGHT) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionX = $this->_imageSrcWidth - imagesx($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_LEFT) {
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_RIGHT) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionX = $this->_imageSrcWidth - imagesx($watermark);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionY = $this->_imageSrcHeight - imagesy($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_LEFT) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $positionY = $this->_imageSrcHeight - imagesy($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        }

        if ($tile === false && $merged === false) {
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesx($watermark),
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } else {
            $offsetX = $positionX;
            $offsetY = $positionY;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            while ($offsetY <= $this->_imageSrcHeight + imagesy($watermark)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                while ($offsetX <= $this->_imageSrcWidth + imagesx($watermark)) {
                    $this->imagecopymergeWithAlphaFix(
                        $this->_imageHandler,
                        $watermark,
                        $offsetX,
                        $offsetY,
                        0,
                        0,
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        imagesx($watermark),
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        imagesy($watermark),
                        $this->getWatermarkImageOpacity()
                    );
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $offsetX += imagesx($watermark);
                }
                $offsetX = $positionX;
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $offsetY += imagesy($watermark);
            }
        }

        return $watermark;
    }

    /**
     * Create watermark.
     *
     * @param resource $watermark
     * @param string $width
     * @param string $height
     * @return false|resource
     */
    private function createWaterMark($watermark, string $width, string $height)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $newWatermark = imagecreatetruecolor($width, $height);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagealphablending($newWatermark, false);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $col = imagecolorallocate($newWatermark, 255, 255, 255);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecolortransparent($newWatermark, $col);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagefilledrectangle($newWatermark, 0, 0, $width, $height, $col);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagesavealpha($newWatermark, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecopyresampled(
            $newWatermark,
            $watermark,
            0,
            0,
            0,
            0,
            $width,
            $height,
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            imagesx($watermark),
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            imagesy($watermark)
        );

        return $newWatermark;
    }

    /**
     * Crop image
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     * @return bool
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        if ($left == 0 && $top == 0 && $right == 0 && $bottom == 0) {
            return false;
        }

        $newWidth = $this->_imageSrcWidth - $left - $right;
        $newHeight = $this->_imageSrcHeight - $top - $bottom;

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->_fileType == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecopyresampled(
            $canvas,
            $this->_imageHandler,
            0,
            0,
            $left,
            $top,
            $newWidth,
            $newHeight,
            $newWidth,
            $newHeight
        );
        $this->imageDestroy();
        $this->_imageHandler = $canvas;
        $this->refreshImageDimensions();
        return true;
    }

    /**
     * Checks required dependencies
     *
     * @return void
     * @throws \RuntimeException If some of the dependencies are missing
     */
    public function checkDependencies()
    {
        foreach ($this->_requiredExtensions as $value) {
            if (!extension_loaded($value)) {
                throw new \RuntimeException("Required PHP extension '{$value}' was not loaded.");
            }
        }
    }

    /**
     * Reassign image dimensions
     *
     * @return void
     */
    public function refreshImageDimensions()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $this->_imageSrcWidth = imagesx($this->_imageHandler);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $this->_imageSrcHeight = imagesy($this->_imageHandler);
    }

    /**
     * Standard destructor. Destroy stored information about image
     */
    public function __destruct()
    {
        $this->imageDestroy();
    }

    /**
     * Helper function to free up memory associated with _imageHandler resource
     *
     * @return void
     */
    private function imageDestroy()
    {
        if (is_resource($this->_imageHandler)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            imagedestroy($this->_imageHandler);
        }
    }

    /**
     * Fixes saving PNG alpha channel
     *
     * @param resource $imageHandler
     * @return void
     */
    private function _saveAlpha($imageHandler)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $background = imagecolorallocate($imageHandler, 0, 0, 0);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecolortransparent($imageHandler, $background);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagealphablending($imageHandler, false);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagesavealpha($imageHandler, true);
    }

    /**
     * Returns rgba array of the specified pixel
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getColorAt($x, $y)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $colorIndex = imagecolorat($this->_imageHandler, $x, $y);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return imagecolorsforindex($this->_imageHandler, $colorIndex);
    }

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font
     * @return \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    public function createPngFromString($text, $font = '')
    {
        $error = false;
        $this->_resized = true;
        try {
            $this->_createImageFromTtfText($text, $font);
        } catch (\Exception $e) {
            $error = true;
        }

        if ($error || empty($this->_imageHandler)) {
            $this->_createImageFromText($text);
        }

        return $this;
    }

    /**
     * Create Image using standard font
     *
     * @param string $text
     * @return void
     */
    protected function _createImageFromText($text)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $width = imagefontwidth($this->_fontSize) * strlen((string)$text);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $height = imagefontheight($this->_fontSize);

        $this->_createEmptyImage($width, $height);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $black = imagecolorallocate($this->_imageHandler, 0, 0, 0);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagestring($this->_imageHandler, $this->_fontSize, 0, 0, $text, $black);
    }

    /**
     * Create Image using ttf font
     *
     * Note: This function requires both the GD library and the FreeType library
     *
     * @param string $text
     * @param string $font
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _createImageFromTtfText($text, $font)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $boundingBox = imagettfbbox($this->_fontSize, 0, $font, $text);
        $width = abs($boundingBox[4] - $boundingBox[0]);
        $height = abs($boundingBox[5] - $boundingBox[1]);

        $this->_createEmptyImage($width, $height);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $black = imagecolorallocate($this->_imageHandler, 0, 0, 0);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = imagettftext(
            $this->_imageHandler,
            $this->_fontSize,
            0,
            0,
            $height - $boundingBox[1],
            $black,
            $font,
            $text
        );
        if ($result === false) {
            throw new \InvalidArgumentException('Unable to create TTF text');
        }
    }

    /**
     * Create empty image with transparent background
     *
     * @param int $width
     * @param int $height
     * @return void
     */
    protected function _createEmptyImage($width, $height)
    {
        $this->_fileType = IMAGETYPE_PNG;
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $image = imagecreatetruecolor($width, $height);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $colorWhite = imagecolorallocatealpha($image, 255, 255, 255, 127);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagealphablending($image, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagesavealpha($image, true);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagefill($image, 0, 0, $colorWhite);
        $this->imageDestroy();
        $this->_imageHandler = $image;
    }

    /**
     * Fix an issue with the usage of imagecopymerge where the alpha channel is lost
     *
     * @param resource $dst_im
     * @param resource $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @param int $pct
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function imagecopymergeWithAlphaFix(
        $dst_im,
        $src_im,
        $dst_x,
        $dst_y,
        $src_x,
        $src_y,
        $src_w,
        $src_h,
        $pct
    ) {
        if ($pct >= 100) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (false === imagealphablending($dst_im, true)) {
                return false;
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
        }

        if ($pct < 0) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $sizeX = imagesx($src_im);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $sizeY = imagesy($src_im);
        if (false === $sizeX || false === $sizeY) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $tmpImg = imagecreatetruecolor($src_w, $src_h);
        if (false === $tmpImg) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagealphablending($tmpImg, false)) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagesavealpha($tmpImg, true)) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagecopy($tmpImg, $src_im, 0, 0, 0, 0, $sizeX, $sizeY)) {
            return false;
        }

        $transparency = (int) (127 - (($pct * 127) / 100));
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagefilter($tmpImg, IMG_FILTER_COLORIZE, 0, 0, 0, $transparency)) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagealphablending($dst_im, true)) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (false === imagesavealpha($dst_im, true)) {
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = imagecopy($dst_im, $tmpImg, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagedestroy($tmpImg);

        return $result;
    }

    /**
     * Create truecolor image copy of current image
     *
     * @return resource
     */
    private function createTruecolorImageCopy()
    {
        $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $newImage = imagecreatetruecolor($this->_imageSrcWidth, $this->_imageSrcHeight);

        if ($isAlpha) {
            $this->_saveAlpha($newImage);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        imagecopy($newImage, $this->_imageHandler, 0, 0, 0, 0, $this->_imageSrcWidth, $this->_imageSrcHeight);

        return $newImage;
    }
}
