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
use fsmaker\Utils;

use function Laravel\Prompts\select;

#[AsCommand(
    name: 'github-run',
    description: 'Ejecuta un workflow de GitHub Actions con gh workflow run'
)]
class GithubRunCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflowsPath = Utils::getFolder() . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows';

        if (!is_dir($workflowsPath)) {
            Utils::echo("* No se encontró la carpeta .github/workflows en este directorio.\n");
            return Command::FAILURE;
        }

        $files = array_values(array_filter(
            scandir($workflowsPath),
            fn($f) => str_ends_with($f, '.yml') || str_ends_with($f, '.yaml')
        ));

        if (empty($files)) {
            Utils::echo("* No se encontraron archivos de workflow en .github/workflows.\n");
            return Command::FAILURE;
        }

        $workflowName = select(
            label: 'Selecciona el workflow a ejecutar',
            options: array_combine($files, $files),
            scroll: min(count($files), 10),
            required: true
        );

        passthru('gh workflow run ' . escapeshellarg($workflowName), $exitCode);

        return $exitCode === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
