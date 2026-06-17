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
use function Laravel\Prompts\confirm;

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
                return $this->createController($modelName) ? Command::SUCCESS : Command::FAILURE;

            case 'ListController':
                $fields = Column::askMulti();
                return $this->createListController($modelName, $fields) ? Command::SUCCESS : Command::FAILURE;

            case 'EditController':
                $fields = Column::askMulti();
                return $this->createEditController($modelName, $fields) ? Command::SUCCESS : Command::FAILURE;
        }

        Utils::echo("Opción no válida.\n");
        return Command::FAILURE;
    }

    private function createController(string $name): bool
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre del controlador.\n");
            return false;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo("* El controlador " . $name . " YA EXISTE.\n");
            return false;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: sales',
            default: 'admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "admin".'
        );

        $createView = 'si' === Utils::promptYesOrNo(
            label: '¿Desea añadir la vista twig?'
        );

        $samplePath = dirname(__DIR__, 3) . "/samples/Controller.php.sample";
        $sample = Utils::readFile($samplePath);
        if ($sample === false) {
            return false;
        }

        if (!$createView) {
            $search = "\n\n        \$this->view('[[NAME]].html.twig');";
            $sample = str_replace($search, '', $sample);
        }

        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[MENU]]'], [Utils::getNamespace(), $name, $menu], $sample);
        if (false === Utils::createFolder($filePath)) {
            return false;
        }
        if (!Utils::writeFile($fileName, $template)) {
            return false;
        }
        Utils::echo('* ' . $fileName . " -> OK.\n");

        if ($createView) {
            $viewPath = Utils::isCoreFolder() ? 'Core/View/' : 'View/';
            $viewFilename = $viewPath . $name . '.html.twig';
            if (false === Utils::createFolder($viewPath)) {
                return false;
            }
            if (file_exists($viewFilename)) {
                Utils::echo('* ' . $viewFilename . " YA EXISTE.\n");
                return true;
            }

            $samplePath2 = dirname(__DIR__, 3) . "/samples/View.html.twig.sample";
            $sample2 = Utils::readFile($samplePath2);
            if ($sample2 === false) {
                return false;
            }
            $template2 = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample2);
            if (!Utils::writeFile($viewFilename, $template2)) {
                return false;
            }
            Utils::echo('* ' . $viewFilename . " -> OK.\n");
        }

        return true;
    }

    private function createEditController(string $modelName, array $fields): bool
    {
        if (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del EditController');
            return false;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'Edit' . $modelName . '.php';
        if (false === Utils::createFolder($filePath)) {
            return false;
        }
        if (file_exists($fileName)) {
            Utils::echo("El controlador " . $fileName . " YA EXISTE.\n");
            return false;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: sales',
            default: 'admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "admin".'
        );

        $samplePath = dirname(__DIR__, 3) . "/samples/EditController.php.sample";
        $sample = Utils::readFile($samplePath);
        if ($sample === false) {
            return false;
        }
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $menu],
            $sample
        );
        if (!Utils::writeFile($fileName, $template)) {
            return false;
        }
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        if (false === Utils::createFolder($xmlPath)) {
            return false;
        }
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return true;
        }

        if (!FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'edit')) {
            return false;
        }
        Utils::echo('* ' . $xmlFilename . " -> OK.\n");

        return true;
    }

    private function createListController(string $modelName, array $fields): bool
    {
        if (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del ListController');
            return false;
        }

        $menu = text(
            label: 'Nombre del menú',
            placeholder: 'Ej: sales',
            default: 'admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "admin".'
        );

        $title = text(
            label: 'Nombre del submenú',
            placeholder: 'Ej: Productos',
            default: $modelName,
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'title\'] = \'NOMBRE_ELEGIDO\';", (Si tienes traducciones coloca la key de la traducción).'
        );

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'List' . $modelName . '.php';
        if (false === Utils::createFolder($filePath)) {
            return false;
        }
        if (file_exists($fileName)) {
            Utils::echo("El controlador " . $fileName . " YA EXISTE.\n");
            return false;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/ListController.php.sample";
        $sample = Utils::readFile($samplePath);
        if ($sample === false) {
            return false;
        }
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]', '[[TITLE]]'],
            [Utils::getNamespace(), $modelName, $menu, $title],
            $sample
        );
        if (!Utils::writeFile($fileName, $template)) {
            return false;
        }
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        if (false === Utils::createFolder($xmlPath)) {
            return false;
        }
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return true;
        }

        if (!FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'list')) {
            return false;
        }
        Utils::echo('* ' . $xmlFilename . " -> OK.\n");

        return true;
    }
}
