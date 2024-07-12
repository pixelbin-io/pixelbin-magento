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

namespace Pixelbinio\Pixelbin\Console;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadImageToPixelBin extends Command
{
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
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Upload image to pixelbin code needs to add

        $output->writeln("Pixelbin image upload command executed :)");
    }
}
