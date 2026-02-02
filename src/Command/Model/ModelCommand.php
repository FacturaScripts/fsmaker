<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Model;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\Utils;

#[AsCommand(
    name: 'model',
    description: 'Crea un nuevo modelo con su tabla XML'
)]
class ModelCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre del modelo (singular)',
            placeholder: 'Ej: Cliente',
            hint: 'El nombre debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $tableName = Utils::prompt(
            label: 'Nombre de la tabla (plural)',
            placeholder: 'Ej: facturascli',
            hint: 'El nombre debe empezar por minuscula y solo puede contener minusculas, números y guiones bajos.',
            regex: '/^[a-z][a-z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por minuscula y solo puede contener minusculas, números y guiones bajos.'
        );

        $filePath = Utils::isCoreFolder() ? 'Core/Model/' : 'Model/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El modelo " . $name . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        $fields = Column::askMulti();
        FileGenerator::createModelByFields($fileName, $tableName, $fields, $name, Utils::getNamespace());
        Utils::echo('* ' . $fileName . " -> OK.\n");

        $tablePath = Utils::isCoreFolder() ? 'Core/Table/' : 'Table/';
        $tableFilename = $tablePath . $tableName . '.xml';
        Utils::createFolder($tablePath);
        if (false === file_exists($tableFilename)) {
            FileGenerator::createTableXmlByFields($tableFilename, $tableName, $fields);
            Utils::echo('* ' . $tableFilename . " -> OK.\n");
        } else {
            Utils::echo("\n" . '* ' . $tableFilename . " YA EXISTE");
        }

        Utils::echo("\n");
        if (Utils::promptYesOrNo('¿Crear EditController? (No - predeterminado)') === 'Si') {
            $this->createEditController($name, $fields);
        }

        Utils::echo("\n");
        if (Utils::promptYesOrNo('¿Crear ListController? (No - predeterminado)') === 'Si') {
            $this->createListController($name, $fields);
        }

        return Command::SUCCESS;
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

        $menu = \Laravel\Prompts\text(
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

        $menu = \Laravel\Prompts\text(
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
