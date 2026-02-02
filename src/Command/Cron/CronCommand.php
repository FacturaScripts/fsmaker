<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;

#[AsCommand(
    name: 'cron',
    description: 'Crea un archivo Cron.php para el plugin'
)]
class CronCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $name = Utils::findPluginName();
        $fileName = "Cron.php";

        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return Command::FAILURE;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Cron.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        return Command::SUCCESS;
    }
}
