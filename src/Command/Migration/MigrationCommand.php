<?php
/**
 * @author fsmaker
 */

namespace fsmaker\Command\Migration;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\InitEditor;
use fsmaker\Utils;

#[AsCommand(
    name: 'migration',
    description: 'Crea una nueva migración'
)]
class MigrationCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePlugin()) {
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre de la migración',
            placeholder: 'Ej: FixTablaUsuarios',
            hint: 'El nombre debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $this->createMigration($name);

        return Command::SUCCESS;
    }

    private function createMigration(string $name): void
    {
        $folder = 'Migration/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo("* La migración " . $name . " YA EXISTE.\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Migration.php.sample";
        $sample = file_get_contents($samplePath);
        
        $migrationNameConst = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . '_' . date('Y_m_d');
        $template = str_replace(
            ['[[NAME]]', '[[NAME_SPACE]]', '[[MIGRATION_NAME]]'],
            [$name, Utils::getNamespace(), $migrationNameConst],
            $sample
        );
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $newContentUse = InitEditor::addUse('use FacturaScripts\Core\Migrations;');
        if ($newContentUse) {
            InitEditor::setInitContent($newContentUse);
        }

        $newContentFunc = InitEditor::addToFunction('update', 'Migrations::runPluginMigration(new Migration\\' . $name . '());', true);
        if ($newContentFunc) {
            InitEditor::setInitContent($newContentFunc);
        }
    }
}
