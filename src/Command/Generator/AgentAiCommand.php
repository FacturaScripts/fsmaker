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

#[AsCommand(
    name: 'agent-ai',
    description: 'Copia los archivos de configuración de IA para Claude Code'
)]
class AgentAiCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Utils::isCoreFolder()) {
            $this->copyFolder(__DIR__ . '/../../../samples/ai-core', getcwd());
            return Command::SUCCESS;
        }

        if (Utils::isPluginFolder()) {
            $this->copyFolder(__DIR__ . '/../../../samples/ai-plugin', getcwd());
            return Command::SUCCESS;
        }

        Utils::echo("* Esta no es la carpeta raíz del plugin o de facturascripts.\n");
        return Command::FAILURE;
    }

    private function copyFolder(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            Utils::echo("* Carpeta de origen no encontrada: $source\n");
            return;
        }

        // Eliminar previamente los directorios y archivos de primer nivel que existan en destino,
        // para que archivos eliminados en nuevas versiones no queden huérfanos.
        foreach (scandir($source) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $targetPath = $destination . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($targetPath)) {
                $this->deleteFolder($targetPath);
            } elseif (is_file($targetPath)) {
                unlink($targetPath);
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($source) + 1);
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                Utils::createFolder($targetPath);
                continue;
            }

            // Saltar archivos del sistema
            if ($item->getFilename() === '.DS_Store') {
                continue;
            }

            Utils::createFolder(dirname($targetPath));

            if (copy($item->getPathname(), $targetPath)) {
                Utils::echo("* $relativePath -> OK.\n");
            }
        }
    }

    private function deleteFolder(string $path): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($path);
    }
}
