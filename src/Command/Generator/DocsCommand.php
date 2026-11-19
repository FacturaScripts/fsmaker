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
use fsmaker\Utils;

/**
 * Crea o completa la estructura de documentación (Doc/, AGENTS.md y CLAUDE.md) de un plugin o del core.
 */
#[AsCommand(
    name: 'docs',
    description: 'Crea o completa la estructura de documentación (Doc, AGENTS.md y CLAUDE.md)'
)]
class DocsCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Utils::isCoreFolder()) {
            FileGenerator::createDocs('FacturaScripts', FileGenerator::getDocsIntro());
            return Command::SUCCESS;
        }

        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta de un plugin o del core.\n");
            return Command::FAILURE;
        }

        FileGenerator::createDocs(Utils::findPluginName(), FileGenerator::getDocsIntro());
        return Command::SUCCESS;
    }
}
