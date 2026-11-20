<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Model;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\Utils;

use function Laravel\Prompts\text;

#[AsCommand(
    name: 'model',
    description: 'Crea un nuevo modelo con su tabla XML'
)]
class ModelCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nombre del modelo (singular)')
            ->addOption('table', null, InputOption::VALUE_REQUIRED, 'Nombre de la tabla (plural)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'Campos: nombre:tipo[(longitud)],... (tipos: serial,integer,float,boolean,string,text,timestamp,date,time)')
            ->addOption('menu', null, InputOption::VALUE_REQUIRED, 'Nombre del menú de los controladores (por defecto: admin)')
            ->addOption('edit-controller', null, InputOption::VALUE_NONE, 'Crear EditController')
            ->addOption('list-controller', null, InputOption::VALUE_NONE, 'Crear ListController');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        // si se pasa --fields, el comando no pregunta nada más
        $fieldsSpec = $input->getOption('fields');
        $interactive = null === $fieldsSpec;
        $menu = $input->getOption('menu') ?? ($interactive ? null : 'admin');

        $name = $input->getOption('name') ?? Utils::prompt(
            label: 'Nombre del modelo (singular)',
            placeholder: 'Ej: Cliente',
            hint: 'El nombre debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $tableName = $input->getOption('table') ?? Utils::prompt(
            label: 'Nombre de la tabla (plural)',
            placeholder: 'Ej: facturascli',
            hint: 'El nombre debe empezar por minuscula y solo puede contener minusculas, números y guiones bajos.',
            regex: '/^[a-z][a-z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por minuscula y solo puede contener minusculas, números y guiones bajos.'
        );

        if (1 !== preg_match('/^[A-Z][a-zA-Z0-9_]*$/', $name) || 1 !== preg_match('/^[a-z][a-z0-9_]*$/', $tableName)) {
            Utils::echo("* Nombre de modelo o tabla inválido.\n");
            return Command::FAILURE;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Model/' : 'Model/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El modelo " . $name . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        try {
            $fields = $interactive ? Column::askMulti() : Column::fromSpec($fieldsSpec);
        } catch (\InvalidArgumentException $e) {
            Utils::echo('* ' . $e->getMessage() . "\n");
            return Command::FAILURE;
        }
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
        if ($interactive ? Utils::promptYesOrNo('¿Crear EditController? (No - predeterminado)') === 'si' : $input->getOption('edit-controller')) {
            $this->createEditController($name, $fields, $menu);
        }

        Utils::echo("\n");
        if ($interactive ? Utils::promptYesOrNo('¿Crear ListController? (No - predeterminado)') === 'si' : $input->getOption('list-controller')) {
            $this->createListController($name, $fields, $menu, $interactive ? null : $name);
        }

        return Command::SUCCESS;
    }

    private function createEditController(string $modelName, array $fields, ?string $menu): void
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

        $menu ??= \Laravel\Prompts\text(
            label: 'Nombre del menú',
            placeholder: 'Ej: sales',
            default: 'admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "admin".'
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

    private function createListController(string $modelName, array $fields, ?string $menu, ?string $title): void
    {
        if (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del ListController');
            return;
        }

        $menu ??= text(
            label: 'Nombre del menú',
            placeholder: 'Ej: sales',
            default: 'admin',
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'menu\'] = \'NOMBRE_ELEGIDO\';", por defecto es "admin".'
        );

        $title ??= text(
            label: 'Nombre del submenú',
            placeholder: 'Ej: Productos',
            default: $modelName,
            required: true,
            validate: null,
            hint: 'El nombre que se colocará en "$data[\'title\'] = \'NOMBRE_ELEGIDO\';", (Si tienes traducciones coloca la key de la traducción).'
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
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]', '[[TITLE]]'],
            [Utils::getNamespace(), $modelName, $menu, $title],
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
