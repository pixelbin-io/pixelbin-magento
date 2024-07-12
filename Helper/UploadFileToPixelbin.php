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

namespace Pixelbinio\Pixelbin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Pixelbin\Platform\PixelbinClient;
use Pixelbin\Platform\PixelbinConfig;
use Pixelbin\Utils\Url;

class UploadFileToPixelbin extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Data    $helperData
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Get pixelbin object
     *
     * @return PixelbinClient
     * @throws LocalizedException
     */
    public function getPixelbinObj()
    {
        try {
            $config = new PixelbinConfig([
                "domain" => $this->helperData->getApiUrl(),
                "apiSecret" => $this->helperData->getAppApiSecret(),
            ]);
            return new PixelbinClient($config);
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __("Unable to create pixelbin object error is => " . $ex->getMessage())
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    public function uploadFile()
    {
        try {
            $pixelbin = $this->getPixelbinObj();
            $result = $pixelbin->assets->listFiles();
            $obj = [
                "cloudName" => $this->helperData->getAppCloudName(),
                "zone" => "z-slug",
                "version" => "v2",
                "options" => [
                    "dpr" => 2.0,
                    "f_auto" => true,
                ],
//                "transformations" => [
//                    [
//                        "plugin" => "t",
//                        "name" => "resize",
//                        "values" => [
//                            [
//                                "key" => "h",
//                                "value" => "100",
//                            ],
//                            [
//                                "key" => "w",
//                                "value" => "200",
//                            ],
//                        ],
//                    ],
//                    [
//                        "plugin" => "t",
//                        "name" => "flip",
//                    ],
//                ],
                "filePath" => "path/to/image.jpeg",
                "baseUrl" => "https://cdn.pixelbin.io",
            ];

            $url = Url::obj_to_url($obj); // $obj is as shown above
        } catch (\Exception $ex) {
            throw new LocalizedException(
                __($ex->getMessage())
            );
        }
    }
}
