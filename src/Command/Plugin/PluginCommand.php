<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Plugin;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\FileGenerator;
use fsmaker\UpdateTranslations;
use fsmaker\Utils;

#[AsCommand(
    name: 'plugin',
    description: 'Crea la estructura básica de un nuevo plugin'
)]
class PluginCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
            Utils::echo("* No se puede crear un plugin en esta carpeta.\n");
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre del plugin',
            placeholder: 'Ej: MiPlugin',
            hint: 'El nombre del plugin debe empezar por mayúscula, sin espacios y sin caracteres especiales.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        if (file_exists($name)) {
            Utils::echo("* El plugin " . $name . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        mkdir($name, 0755);
        Utils::echo('* ' . $name . " -> OK.\n");

        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES',
            'Extension/Controller', 'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View',
            'Model/Join', 'Table', 'Translation', 'View', 'XMLView', 'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        foreach ($folders as $folder) {
            Utils::createFolder($name . '/' . $folder);
            touch($name . '/' . $folder . '/.gitignore');
        }

        UpdateTranslations::create($name);

        chdir($name);
        FileGenerator::createIni($name);
        FileGenerator::createGitIgnore();
        $this->createCron($name);
        $this->createInit();

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

    private function createInit(): void
    {
        $fileName = "Init.php";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Init.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace('[[NAME]]', Utils::findPluginName(), $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");
    }
}
