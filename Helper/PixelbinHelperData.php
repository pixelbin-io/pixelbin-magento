<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Pixelbinio\Pixelbin\Logger\Logger;

class PixelbinHelperData extends AbstractHelper
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Data construct
     *
     * @param Context $context
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Parse array creation for Pixelbin URL
     *
     * @method parseArrayCreation
     * @param  string $url
     * @param  string|null $parsedUrlParts
     * @return array
     */
    public function parseArrayCreation($url, $parsedUrlParts)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $parsed = [
            "orig_url" => $url,
            "scheme" => isset($parsedUrlParts["scheme"]) ? $parsedUrlParts["scheme"] : null,
            "host" => isset($parsedUrlParts["host"]) ? $parsedUrlParts["host"] : null,
            "path" => isset($parsedUrlParts["path"]) ? $parsedUrlParts["path"] : null,
            "query" => isset($parsedUrlParts["query"]) ? $parsedUrlParts["query"] : null,
            "extension" => $extension,
            "type" => null,
            "cloudName" => null,
            "version" => null,
            "publicId" => ltrim((string) $publicId, '/') ?: null,
            "transformations_string" => null,
            "transformations" => [],
            "transformationless_url" => $url,
            "versionless_url" => $url,
            "versionless_transformationless_url" => $url,
            "thumbnail_url" => null,
        ];

        return $parsed;
    }

    /**
     * Parse Pixelbin URL
     *
     * @method parsePixelbinUrl
     * @param  string $url
     * @param  string|null $publicId
     * @return array
     */
    public function parsePixelbinUrl($url, $publicId = null)
    {
        $url = preg_replace('/\?.*/', '', $url);
        $parsedUrlParts = $this->mbParseUrl($url);

        $parsed = $this->parseArrayCreation($url, $parsedUrlParts);

        $_url = ltrim($parsed["path"], '/');
        $_url = preg_replace('/\.[^.]+$/', '', $_url);

        preg_match('/\/v[0-9]{1,10}\//', $_url, $version);
        if ($version && isset($version[0])) {
            $parsed["version"] = trim($version[0], '/');
        }

        if (!$parsed["publicId"] && $parsed["version"]) {
            $parsed["publicId"] = preg_replace('/.+\/v[0-9]{1,10}\//', '', $_url);
        }

        //@codingStandardsIgnoreStart
        $_url = preg_replace('/(\/|\/v[0-9]{1,10}\/)' . \preg_quote((string) $parsed["publicId"], '/') . '$/', '', $_url);
        //@codingStandardsIgnoreEnd

        $_url = explode('/', $_url);

        $slug = \array_shift($_url);
        if (\in_array($slug, ["image","video"])) {
            $parsed["type"] = $slug;
        } else {
            $parsed["cloudName"] = $slug;
        }

        $slug = \array_shift($_url);
        $parsed["type"] = ($parsed["cloudName"] && $slug  === "video") ? "video" : "image";

        if (isset($parsed['extension'])) {
            $parsed['type'] = (in_array($parsed['extension'], $this->getSupportedVideoFormats())) ? 'video' : 'image';
        }

        $slug = \array_shift($_url);
        $parsed["transformations_string"] = ($slug === 'upload' ? '' : $slug) . implode('/', $_url);

        if ($parsed["transformations_string"]) {
            $parsed["transformations"] = explode(',', \str_replace('/', ',', $parsed["transformations_string"]));
            // @codingStandardsIgnoreLine
            $parsed["transformationless_url"] = preg_replace('/\/' . \preg_quote($parsed["transformations_string"], '/') . '\//', '/', $url, 1);
        }

        $parsed["versionless_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $url, 1);
        // @codingStandardsIgnoreLine
        $parsed["versionless_transformationless_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $parsed["transformationless_url"], 1);

        if ($parsed["type"] === "video") {
            $parsed["thumbnail_url"] = preg_replace('/\.[^.]+$/', '', $url);
            $parsed["thumbnail_url"] = preg_replace('/\/v[0-9]{1,10}\//', '/', $parsed["thumbnail_url"]);
            // @codingStandardsIgnoreLine
            $parsed["thumbnail_url"] = preg_replace('/\/(' . \preg_quote((string) $parsed["publicId"], '/') . ')$/', '/so_auto/$1.jpg', $parsed["thumbnail_url"]);
        }
        return $parsed;
    }

    /**
     * UTF-8 aware parse_url() replacement.
     *
     * @param string $url
     * @param string|int $component
     * @return array|int|string
     */
    public function mbParseUrl($url, $component = -1)
    {
        $enc_url = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $url
        );

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parts = parse_url($enc_url, $component);
        if ($parts === false) {
            throw new \InvalidArgumentException('Malformed URL: ' . $url);
        }
        if (is_array($parts)) {
            foreach ($parts as $name => $value) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $parts[$name] = rawurldecode($value);
            }
        } else {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $parts = rawurldecode($parts);
        }
        return $parts;
    }
}
