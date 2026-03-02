<?php
/**
 * @author Abderrahim Darghal Belkacemi  <abdedarghal111@gmail.com>
 */

namespace fsmaker\Command\View;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;

#[AsCommand(
    name: 'view',
    description: 'Crea una vista Twig en la carpeta View'
)]
class ViewCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre de la vista',
            placeholder: 'Ej: MiVista',
            hint: 'El nombre debe empezar por mayúscula. Tendrá el formato "{NOMBRE}.html.twig"',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        return $this->createView($name);
    }

    /**
     * Crea una vista dentro del core o dentro del plugin dado el nombre.
     * 
     * Siendo Core/View o View/ respectivamente.
     */
    private function createView(string $name): int
    {
        $viewPath = '';
        if (Utils::isCoreFolder()) {
            $viewPath = 'Core/View/';
        }
        if (Utils::isPluginFolder()) {
            $viewPath = 'View/';
        }

        if ($viewPath == '') {
            $this->requirePluginOrCore(); // que suelte el mensaje de error
            return Command::FAILURE;
        }

        $fileName = $viewPath . $name . '.html.twig';

        Utils::createFolder($viewPath);

        if (file_exists($fileName)) {
            Utils::echo("* La vista " . $fileName . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/View.html.twig.sample";
        $sample = file_get_contents($samplePath);

        file_put_contents($fileName, $sample);

        Utils::echo('* ' . $fileName . " -> OK.\n");

        return Command::SUCCESS;
    }
}
