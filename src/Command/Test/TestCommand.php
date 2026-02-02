<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Test;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;

#[AsCommand(
    name: 'test',
    description: 'Crea un nuevo test para el plugin'
)]
class TestCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Utils::isCoreFolder() || false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre del test (singular)',
            placeholder: 'Ej: AccountingPlanTest',
            hint: 'El nombre del test debe empezar por mayúscula y terminar en Test',
            regex: '/^[A-Z][a-zA-Z0-9_]*Test$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y terminar en Test'
        );

        $filePath = 'Test/main/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El test " . $name . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        $txtFile = $filePath . 'install-plugins.txt';
        if (false === file_exists($txtFile)) {
            // Creamos el fichero install-plugins.txt con el nombre del plugin
            $ini = parse_ini_file('facturascripts.ini');
            file_put_contents($txtFile, $ini['name'] ?? '');
            Utils::echo('* ' . $txtFile . " -> OK.\n");
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Test.php.sample";
        $sample = file_get_contents($samplePath);
        $nameSpace = Utils::getNamespace() . '\\' . str_replace('/', '\\', substr($filePath, 0, -1));
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$nameSpace, $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        return Command::SUCCESS;
    }
}
