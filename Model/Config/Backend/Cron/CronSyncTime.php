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

namespace Pixelbinio\Pixelbin\Model\Config\Backend\Cron;

use Exception;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Message\ManagerInterface;

class CronSyncTime extends Value
{
    /**
     * Cron scan path
     */
    public const GENERATE_SCAN_PATH = 'crontab/default/jobs/pixelbin_image_sync/schedule/cron_expr';

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param ManagerInterface $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        ScopeConfigInterface                                    $config,
        \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList,
        ValueFactory                                            $configValueFactory,
        ManagerInterface                                        $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        array                                                   $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->configValueFactory = $configValueFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * After Save method
     *
     * @return CronSyncTime
     */
    public function afterSave()
    {
        $generateSchedule = $this->getData('groups/pixelbin_image_sync/fields/image_sync_time/value');
        try {
            $this->configValueFactory->create()->load(
                self::GENERATE_SCAN_PATH,
                'path'
            )->setValue(
                $generateSchedule
            )->setPath(
                self::GENERATE_SCAN_PATH
            )->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t save the cron expression. %1', $e->getMessage()));
        }
        return parent::afterSave();
    }
}
