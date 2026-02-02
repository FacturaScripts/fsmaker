<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Command\Worker;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\InitEditor;
use fsmaker\Utils;

use function Laravel\Prompts\multiselect;

#[AsCommand(
    name: 'worker',
    description: 'Crea un nuevo Worker y lo registra en Init.php'
)]
class WorkerCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return Command::FAILURE;
        }

        $name = Utils::prompt(
            label: 'Nombre del worker',
            placeholder: 'Ej: MiWorker',
            hint: 'El nombre debe empezar por mayúscula y contener solo texto, números o guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        $filePath = 'Worker/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El worker " . $name . " YA EXISTE.\n");
            return Command::FAILURE;
        }

        $samplePath = dirname(__DIR__, 3) . "/samples/Worker.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);

        Utils::echo('* ' . $fileName . " -> OK.\n");
        $options = multiselect(
            label: '¿Qué eventos debe escuchar el worker?',
            options: [
                '1' => 'Insert',
                '2' => 'Update',
                '3' => 'Save',
                '4' => 'Delete',
                '5' => 'Todos',
                '6' => 'Personalizado'
            ],
            scroll: 6,
            required: true
        );

        // si en las opciones esta algunos de los números del 1 al 5, preguntamos el modelo
        // y lo añadimos a la lista de opciones
        if (in_array(1, $options)
            || in_array(2, $options)
            || in_array(3, $options)
            || in_array(4, $options)
            || in_array(5, $options)) {
            $event = Utils::prompt(
                label: 'Introduce el nombre del modelo que contiene el evento a escuchar',
                placeholder: 'Ej: FacturaCliente',
                hint: 'El nombre debe empezar por mayúscula y contener solo texto, números o guiones bajos.',
                regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
            );
        } elseif (in_array(6, $options)) {
            $event = Utils::prompt(
                label: 'Introduce el nombre del evento',
                hint: 'El nombre debe contener solo texto, números o guiones.',
                regex: '/^[a-zA-Z0-9_-]*$/',
                errorMessage: 'Inválido, debe contener solo texto, números o guiones.'
            );
        } else {
            Utils::echo("* Error(Input): Opción no válida.\n");
            return Command::FAILURE;
        }

        // si el evento está vacío, no se ha introducido nada
        if (empty($event)) {
            Utils::echo("* El evento no puede estar vacío.\n");
            return Command::FAILURE;
        }

        // agregar la dependencia
        $modifiedInit = InitEditor::addUse('use FacturaScripts\Core\WorkQueue;');
        if ($modifiedInit !== null) {
            InitEditor::setInitContent($modifiedInit);
        }

        // aplicar los eventos
        foreach ($options as $option) {
            switch ($option) {
                case 1:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'Model.' . $event . '.Insert\');', true);
                    break;

                case 2:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'Model.' . $event . '.Update\');', true);
                    break;

                case 3:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'Model.' . $event . '.Save\');', true);
                    break;

                case 4:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'Model.' . $event . '.Delete\');', true);
                    break;

                case 5:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'Model.' . $event . '.*\');', true);
                    break;

                case 6:
                    $newContent = InitEditor::addToInitFunction('WorkQueue::addWorker(\'' . $name . '\', \'' . $event . '\');', true);
                    break;

                default:
                    Utils::echo("* Error(Input): Opción no válida.\n");
                    return Command::FAILURE;
            }

            if ($newContent) {
                InitEditor::setInitContent($newContent);
            }
        }

        return Command::SUCCESS;
    }
}
