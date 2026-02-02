<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Controller;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\Utils;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[AsCommand(
    name: 'controller',
    description: 'Crea un nuevo controlador (Controller, ListController o EditController)'
)]
class ControllerCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $option = select(
            label: 'Elija el tipo de controlador a crear',
            options: [
                'Controller' => 'Controller',
                'ListController' => 'ListController',
                'EditController' => 'EditController'
            ],
            default: 'Controller',
            scroll: 3,
            required: true
        );

        $modelName = Utils::prompt(
            label: "Nombre del $option",
            placeholder: 'Ej: Producto',
            hint: "El nombre del $option debe empezar por mayúscula y solo puede contener letras, números y guiones bajos, luego será colocado como 'List[Nombre elegido].php' por ejemplo.",
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        switch ($option) {
            case 'Controller':
                $this->createController($modelName);
                return Command::SUCCESS;

            case 'ListController':
                $fields = Column::askMulti();
                $this->createListController($modelName, $fields);
                return Command::SUCCESS;

            case 'EditController':
                $fields = Column::askMulti();
                $this->createEditController($modelName, $fields);
                return Command::SUCCESS;
        }

        Utils::echo("Opción no válida.\n");
        return Command::FAILURE;
    }

    private function createController(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre del controlador.\n");
            return;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo("* El controlador " . $name . " YA EXISTE.\n");
            return;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: Ventas',
            default: 'Admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "Admin".'
        );

        $samplePath = dirname(__DIR__, 3) . "/samples/Controller.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[MENU]]'], [Utils::getNamespace(), $name, $menu], $sample);
        Utils::createFolder($filePath);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $viewPath = Utils::isCoreFolder() ? 'Core/View/' : 'View/';
        $viewFilename = $viewPath . $name . '.html.twig';
        Utils::createFolder($viewPath);
        if (file_exists($viewFilename)) {
            Utils::echo('* ' . $viewFilename . " YA EXISTE.\n");
            return;
        }

        $samplePath2 = dirname(__DIR__, 3) . "/samples/View.html.twig.sample";
        $sample2 = file_get_contents($samplePath2);
        $template2 = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample2);
        file_put_contents($viewFilename, $template2);
        Utils::echo('* ' . $viewFilename . " -> OK.\n");
    }

    private function createEditController(string $modelName, array $fields): void
    {
        if (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del EditController');
            return;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'Edit' . $modelName . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("El controlador " . $fileName . " YA EXISTE.\n");
            return;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: Ventas',
            default: 'Admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "Admin".'
        );

        $samplePath = dirname(__DIR__, 3) . "/samples/EditController.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'edit');
        Utils::echo('* ' . $xmlFilename . " -> OK.\n");
    }

    private function createListController(string $modelName, array $fields): void
    {
        if (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del ListController');
            return;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: Ventas',
            default: 'Admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "Admin".'
        );

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'List' . $modelName . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("El controlador " . $fileName . " YA EXISTE.\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/ListController.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'list');
        Utils::echo('* ' . $xmlFilename . " -> OK.\n");
    }
}
