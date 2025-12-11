<?php

/**
 * Copyright © 2023 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pixelbinio\Pixelbin\Console;

use Exception;
use Magento\MediaStorage\Model\File\Storage as StorageModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pixelbinio\Pixelbin\Helper\Data as HelperData;
use Pixelbinio\Pixelbin\Helper\UploadFileToPixelbin;
use Pixelbinio\Pixelbin\Model\Config\Source\SyncType;

class UploadImageToPixelBin extends Command
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StorageModel
     */
    protected $storageModel;

    /**
     * @var UploadFileToPixelbin
     */
    protected $uploadFileToPixelbin;

    /**
     * UploadImageToPixelBin construct
     *
     * @param HelperData $helperData
     * @param StorageModel $storageModel
     * @param UploadFileToPixelbin $uploadFileToPixelbin
     * @param string|null $name
     */
    public function __construct(
        HelperData $helperData,
        StorageModel $storageModel,
        UploadFileToPixelbin $uploadFileToPixelbin,
        ?string $name = null
    ) {
        $this->helperData = $helperData;
        $this->storageModel = $storageModel;
        $this->uploadFileToPixelbin = $uploadFileToPixelbin;
        parent::__construct($name);
    }

    /**
     * Configure the console command name and description
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('pixelbin:upload:all');
        $this->setDescription('This CLI command will migrate the images to Pixelbin');
        parent::configure();
    }

    /**
     * Upload image to pixelbin
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|void|int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Upload image to pixelbin code needs to add
        if (!$this->helperData->isModuleEnabled()) {
            $output->writeln('');
            $output->writeln("<error>This option is not enabled, check module configuration settings.</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $output->writeln('');
        $output->writeln('<comment>Start Importing Media Files</comment>');
        $output->writeln('');
        $flag = true;
        try {
            $this->syncMedia($output);
        } catch (\Exception $e) {
            $flag = false;
            $output->writeln('');
            $output->writeln("<error>{$e->getMessage()}</error>");
        }

        $output->writeln('');
        $output->writeln('');
        if ($flag) {
            $output->writeln('<info>Media Files Imported Successfully</info>');
            $output->writeln('<info>Media Storage Set To Pixelbin</info>');
        } else {
            $output->writeln('<error>Unable To Import</error>');
        }
        return 1;
    }

    /**
     * Sync Media
     *
     * @param OutputInterface $output
     */
    private function syncMedia($output)
    {
        $sourceModel = $this->storageModel->getStorageModel();
        $offset = 0;
        $steps = $this->helperData->getTotalSteps($sourceModel);
        $progressBar = new ProgressBar($output, $steps);
        $progressBar->setBarWidth(50);
        $progressBar->setFormat('verbose');
        $progressBar->setProgressCharacter('<info>➤</info>');
        $progressBar->setBarCharacter('<info>=</info>');
        $progressBar->start();
        $successCount = [];
        $errorCount = [];
        $excludeFolderCounts = [];
        $excludeExtensionCounts = [];
        while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
            $progressBar->advance();
            $uploadResponse = $this->uploadFileToPixelbin->importFiles($files, SyncType::TYPE_CLI);
            if (!empty($uploadResponse['successCount'])) {
                $successCount[] = $uploadResponse['successCount'];
            }
            if (!empty($uploadResponse['errorCount'])) {
                $errorCount[] = $uploadResponse['errorCount'];
            }
            if (!empty($uploadResponse['excludeFolderCounts'])) {
                $excludeFolderCounts[] = $uploadResponse['excludeFolderCounts'];
            }
            if (!empty($uploadResponse['excludeExtensionCounts'])) {
                $excludeExtensionCounts[] = $uploadResponse['excludeExtensionCounts'];
            }
            $offset += count($files);
        }
        $progressBar->finish();
        unset($files);
        $output->writeln("");
        $output->writeln("Successfully uploaded file count is => " . count($successCount));
        $output->writeln("<error>Failed to uploaded file count is => " . count($errorCount) . "</error>");
        $output->writeln("<error>Skipped due to folder restriction => " . count($excludeFolderCounts) . "</error>");
        $output->writeln(
            "<error>Skipped due to file extension restriction => " . count($excludeExtensionCounts) . "</error>"
        );
    }
}
