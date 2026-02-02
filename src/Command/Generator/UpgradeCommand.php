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
    name: 'upgrade',
    description: 'Actualiza archivos PHP, XML, Twig e INI del plugin'
)]
class UpgradeCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        FileUpdater::upgradePhpFiles();
        FileUpdater::upgradeXmlFiles();
        FileUpdater::upgradeTwigFiles();
        FileUpdater::upgradeIniFile();

        return Command::SUCCESS;
    }
}
