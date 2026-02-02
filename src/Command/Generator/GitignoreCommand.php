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

#[AsCommand(
    name: 'gitignore',
    description: 'Crea un archivo .gitignore para FacturaScripts'
)]
class GitignoreCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        FileGenerator::createGitIgnore();
        return Command::SUCCESS;
    }
}
