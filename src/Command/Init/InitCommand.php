<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Init;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;

#[AsCommand(
    name: 'init',
    description: 'Crea un archivo Init.php para el plugin'
)]
class InitCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePlugin()) {
            return Command::FAILURE;
        }

        $fileName = "Init.php";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return Command::FAILURE;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Init.php.sample";
        $sample = Utils::readFile($samplePath);
        if ($sample === false) {
            return Command::FAILURE;
        }
        $template = str_replace('[[NAME]]', Utils::findPluginName(), $sample);
        if (!Utils::writeFile($fileName, $template)) {
            return Command::FAILURE;
        }
        Utils::echo('* ' . $fileName . " -> OK.\n");

        return Command::SUCCESS;
    }
}
