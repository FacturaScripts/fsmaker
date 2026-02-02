<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Generator;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\FileUpdater;

#[AsCommand(
    name: 'upgrade-bs5',
    description: 'Actualiza las vistas XML de Bootstrap 4 a Bootstrap 5'
)]
class UpgradeBs5Command extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        FileUpdater::upgradeBootstrap5();
        return Command::SUCCESS;
    }
}
