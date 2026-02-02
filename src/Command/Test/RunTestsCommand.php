<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Test;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\RunTests;

#[AsCommand(
    name: 'run-tests',
    description: 'Ejecuta los tests de FacturaScripts'
)]
class RunTestsCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'Ruta a la instalación de FacturaScripts',
            ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        RunTests::run($path);
        return Command::SUCCESS;
    }
}
