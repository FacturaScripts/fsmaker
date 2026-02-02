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
    name: 'cronjob',
    description: 'Crea un nuevo CronJob y lo registra en Cron.php'
)]
class CronJobCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre del CronJob',
            placeholder: 'Ej: MiCronJob',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $folder = 'CronJob/';
        $plugin = Utils::findPluginName();
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return Command::FAILURE;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/CronJob.php.sample";
        $sample = file_get_contents($samplePath);
        $jobName = Utils::kebab($name);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[JOB_NAME]]'], [Utils::getNamespace(), $name, $jobName], $sample);

        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        if (file_exists('Cron.php')) {
            $this->updateCron($name);
        } else {
            $this->createCron($plugin);
            $this->updateCron($name);
        }

        return Command::SUCCESS;
    }

    private function createCron(string $name): void
    {
        $fileName = "Cron.php";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Cron.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");
    }

    private function updateCron(string $name): void
    {
        $fileStr = file_get_contents('Cron.php');
        $newJob = <<<END
        \n
                \$this->job($name::JOB_NAME)
                    ->everyDayAt(8)
                    ->run(function () {
                        $name::run();
                    });
        \n
        END;
        $search = 'public function run(): void';
        $position = strpos($fileStr, $search);
        $nameSpace = Utils::getNamespace();
        if ($position !== false) {
            $position = strpos($fileStr, '{', $position) + 1;
            $fileStr = substr_replace($fileStr, $newJob, $position, 0);
            file_put_contents('Cron.php', $fileStr);
            $usePosition = strpos($fileStr, 'use FacturaScripts\Core\Template\CronClass');
            $usePosition = strpos($fileStr, ';', $usePosition) + 1;
            $fileStr = substr_replace($fileStr, "\nuse $nameSpace\CronJob\\$name;", $usePosition, 0);
            file_put_contents('Cron.php', $fileStr);
            Utils::echo('* Cron.php actualizado' . " -> OK.\n");
        }
    }
}
