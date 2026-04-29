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
use fsmaker\FileGenerator;

use function Laravel\Prompts\select;

#[AsCommand(
    name: 'github-action',
    description: 'Gestiona archivos de GitHub Actions para el plugin'
)]
class GithubActionCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePlugin()) {
            return Command::FAILURE;
        }

        $option = select(
            label: 'Selecciona qué archivo(s) de GitHub Actions crear',
            options: [
                'all'     => '0 - Crear todas las acciones disponibles',
                'test'    => '1 - Crear acción de tests',
                'release' => '2 - Crear acción de release',
            ],
            default: 'all',
            scroll: 3,
            required: true
        );

        switch ($option) {
            case 'all':
                FileGenerator::createGithubActionTest();
                FileGenerator::createGithubActionRelease();
                break;

            case 'test':
                FileGenerator::createGithubActionTest();
                break;

            case 'release':
                FileGenerator::createGithubActionRelease();
                break;
        }

        return Command::SUCCESS;
    }
}
