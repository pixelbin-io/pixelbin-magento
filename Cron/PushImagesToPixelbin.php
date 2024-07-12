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

namespace Pixelbinio\Pixelbin\Cron;

use Pixelbinio\Pixelbin\Logger\Logger;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;

class PushImagesToPixelbin
{
    protected $_logger;
    protected $_helperData;

    public function __construct(
        Logger $logger,
        HelperData $helperData,
    ) {
        $this->_logger = $logger;
        $this->_helperData = $helperData;
    }

    public function execute()
    {
        $log_data = $result = "";
        $isModuleEnabled = $this->_helperData->isModuleEnabled();

        if (!$isModuleEnabled) {
            return;
        }

        $response = $this->_helperData->uploadFileToPixelbin();

        $this->_logger->info($response);
    }


}
