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

use function Laravel\Prompts\text;

#[AsCommand(
    name: 'github-run',
    description: 'Ejecuta un workflow de GitHub Actions con gh workflow run'
)]
class GithubRunCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflowName = text(
            label: 'Nombre del workflow a ejecutar',
            placeholder: 'Ej: tests.yml o tests',
            required: true,
            hint: 'Si no incluyes la extensión .yml se añadirá automáticamente.'
        );

        if (empty($workflowName)) {
            Utils::echo("* No se introdujo el nombre del workflow.\n");
            return Command::FAILURE;
        }

        if (!str_ends_with($workflowName, '.yml') && !str_ends_with($workflowName, '.yaml')) {
            $workflowName .= '.yml';
        }

        passthru('gh workflow run ' . escapeshellarg($workflowName), $exitCode);

        return $exitCode === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
