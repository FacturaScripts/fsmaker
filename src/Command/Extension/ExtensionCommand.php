<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Extension;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\InitEditor;
use fsmaker\Utils;

use function Laravel\Prompts\select;

#[AsCommand(
    name: 'extension',
    description: 'Crea una extensión de tabla, modelo, controlador, XMLView o vista'
)]
class ExtensionCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePluginOrCore()) {
            return Command::FAILURE;
        }

        $option = (int)select(
            label: 'Elija el tipo de extensión',
            options: [
                '1' => 'Tabla',
                '2' => 'Modelo',
                '3' => 'Controlador',
                '4' => 'XMLView',
                '5' => 'View'
            ],
            default: '1',
            scroll: 5,
            required: true
        );

        switch ($option) {
            case 1:
                $name = Utils::prompt(
                    label: 'Nombre de la tabla (plural)',
                    placeholder: 'Ej: productos',
                    hint: 'El nombre de la tabla debe empezar por minúscula y solo puede contener minusculas, números y guiones bajos.',
                    regex: '/^[a-z][a-z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por minúscula y solo puede contener minusculas, números y guiones bajos.'
                );
                $this->createExtensionTable($name);
                return Command::SUCCESS;

            case 2:
                $name = Utils::prompt(
                    label: 'Nombre del modelo (singular)',
                    placeholder: 'Ej: Producto',
                    hint: 'El nombre del modelo debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );
                $this->createExtensionModel($name);
                return Command::SUCCESS;

            case 3:
                $name = Utils::prompt(
                    label: 'Nombre del controlador',
                    placeholder: 'Ej: ListFacturaCliente',
                    hint: 'El nombre del controlador debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );
                $this->createExtensionController($name);
                return Command::SUCCESS;

            case 4:
                $name = Utils::prompt(
                    label: 'Nombre del XMLView',
                    placeholder: 'Ej: EditContacto',
                    hint: 'El nombre del XMLView debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );
                $this->createExtensionXMLView($name);
                return Command::SUCCESS;

            case 5:
                $name = Utils::prompt(
                    label: 'Nombre de la vista html.twig',
                    placeholder: 'Ej: factura_detalle_01',
                    hint: 'El nombre de la vista debe tener el formato: palabra_palabra_número (pudiendo ser mayuscula o minúscula).',
                    regex: '/^[a-zA-Z]+_[a-zA-Z]+_[0-9]+$/',
                    errorMessage: 'Inválido, debe tener el formato: palabra_palabra_número (pudiendo ser mayuscula o minúscula).'
                );
                $this->createExtensionView($name);
                return Command::SUCCESS;
        }

        Utils::echo("* Opción no válida.\n");
        return Command::FAILURE;
    }

    private function createExtensionController(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre del controlador a extender.\n");
            return;
        }

        $folder = 'Extension/Controller/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo("* La extensión del controlador " . $name . " YA EXISTE.\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/ExtensionController.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, Utils::getNamespace()], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . "\n");

        $newContent = InitEditor::addToInitFunction('$this->loadExtension(new Extension\Controller\\' . $name . '());');
        if ($newContent) {
            InitEditor::setInitContent($newContent);
        }
    }

    private function createExtensionModel(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre del modelo a extender.\n");
            return;
        }

        $folder = 'Extension/Model/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo("* La extensión del modelo " . $name . " YA EXISTE.\n");
            return;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/ExtensionModel.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, Utils::getNamespace()], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . "\n");

        $newContent = InitEditor::addToInitFunction(
            '$this->loadExtension(new Extension\Model\\' . $name . '());',
            true
        );

        if ($newContent) {
            InitEditor::setInitContent($newContent);
        }
    }

    private function createExtensionTable(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre de la tabla a extender.\n");
            return;
        }

        $folder = 'Extension/Table/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.xml';
        if (file_exists($fileName)) {
            Utils::echo("* La extensión de la tabla " . $name . " YA EXISTE.\n");
            return;
        }

        $fields = Column::askMulti(true);
        FileGenerator::createTableXmlByFields($fileName, $name, $fields);
        Utils::echo('* ' . $fileName . " -> OK.\n");
    }

    private function createExtensionXMLView(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre del XMLView a extender.\n");
            return;
        }

        $folder = 'Extension/XMLView/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.xml';
        if (file_exists($fileName)) {
            Utils::echo("* El fichero " . $fileName . " YA EXISTE.\n");
            return;
        }

        // comprobamos si el $name empieza por List o Edit
        if (strpos($name, 'List') === 0) {
            $type = 'list';
        } else {
            $type = 'edit';
        }

        $fields = Column::askMulti(true);
        FileGenerator::createXMLViewByFields($fileName, $fields, $type, true);
        Utils::echo('* ' . $fileName . " -> OK.\n");
    }

    private function createExtensionView(string $name): void
    {
        if (empty($name)) {
            Utils::echo("* No introdujo el nombre de la vista a extender.\n");
            return;
        }

        $folder = 'Extension/View/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.html.twig';
        if (file_exists($fileName)) {
            Utils::echo("* El fichero " . $fileName . " YA EXISTE.\n");
            return;
        }

        file_put_contents($fileName, '');
        Utils::echo('* ' . $fileName . " -> OK.\n");
    }
}
