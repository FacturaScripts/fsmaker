<?php
/**
 * @author Carlos García Gómez            <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez       <hola@danielfg.es>
 * @author Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 */

if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

include $_composer_autoload_path ?? __DIR__ . '/vendor/autoload.php';

use fsmaker\ApiGenerator;
use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\FileUpdater;
use fsmaker\InitEditor;
use fsmaker\RunTests;
use fsmaker\UpdateTranslations;
use fsmaker\Utils;
use fsmaker\ZipGenerator;

final class fsmaker
{
    const VERSION = 1.4;
    const OK = " -> OK.\n";

    public function __construct($argv)
    {
        if (count($argv) < 2) {
            $this->help();
            return;
        }

        Utils::setFolder(__DIR__);

        switch ($argv[1]) {
            case 'api':
                ApiGenerator::generate();
                break;

            case 'controller':
                $this->createControllerAction();
                break;

            case 'cron':
                $name = Utils::findPluginName();
                $this->createCron($name);
                break;

            case 'cronjob':
                $this->createCronjob();
                break;

            case 'extension':
                $this->createExtensionAction();
                break;

            case 'gitignore':
                FileGenerator::createGitIgnore();
                break;

            case 'init':
                $this->createInit();
                break;

            case 'model':
                $this->createModelAction();
                break;

            case 'plugin':
                $this->createPluginAction();
                break;

            case 'run-tests':
                RunTests::run($argv[2]);
                break;

            case 'test':
                $this->createTestAction();
                break;

            case 'translations':
                UpdateTranslations::run();
                break;

            case 'upgrade':
                $this->upgradeAction();
                break;

            case 'upgrade-bs5':
                FileUpdater::upgradeBootstrap5();
                break;

            case 'worker':
                $this->createWorkerAction();
                break;

            case 'zip':
                ZipGenerator::generate();
                break;

            default:
                $this->help();
                break;
        }
    }

    private function createController(): void
    {
        $name = Utils::prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . $name . '.php';
        if (file_exists($fileName)) {
            echo "* El controlador " . $name . " YA EXISTE.\n";
            return;
        } elseif (empty($name)) {
            echo "* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n";
            return;
        }

        $menu = Utils::prompt('Menú');
        $sample = file_get_contents(__DIR__ . "/samples/Controller.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[MENU]]'], [Utils::getNamespace(), $name, $menu], $sample);
        Utils::createFolder($filePath);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $viewPath = Utils::isCoreFolder() ? 'Core/View/' : 'View/';
        $viewFilename = $viewPath . $name . '.html.twig';
        Utils::createFolder($viewPath);
        if (file_exists($viewFilename)) {
            echo '* ' . $viewFilename . " YA EXISTE.\n";
            return;
        }

        $sample2 = file_get_contents(__DIR__ . "/samples/View.html.twig.sample");
        $template2 = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample2);
        file_put_contents($viewFilename, $template2);
        echo '* ' . $viewFilename . self::OK;
    }

    private function createControllerAction(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $option = (int)Utils::prompt("Elija el tipo de controlador a crear\n1=Controller, 2=ListController, 3=EditController");
        switch ($option) {
            case 1:
                $this->createController();
                return;

            case 2:
                $modelName = Utils::prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $fields = Column::askMulti();
                $this->createListController($modelName, $fields);
                return;

            case 3:
                $modelName = Utils::prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $fields = Column::askMulti();
                $this->createEditController($modelName, $fields);
                return;
        }

        echo "Opción no válida.\n";
    }

    private function createEditController(string $modelName, array $fields): void
    {
        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'Edit' . $modelName . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            echo "El controlador " . $fileName . " YA EXISTE.\n";
            return;
        } elseif (empty($modelName)) {
            return;
        }

        $menu = Utils::prompt('Menú');
        $sample = file_get_contents(__DIR__ . "/samples/EditController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'edit');
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createListController(string $modelName, array $fields): void
    {
        $menu = Utils::prompt('Menú');
        $title = Utils::prompt('Título');
        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'List' . $modelName . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El controlador " . $fileName . " YA EXISTE.\n";
            return;
        } elseif (empty($modelName)) {
            echo '* No introdujo el nombre del Controlador';
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/ListController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $title, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'list');
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createCron(string $name): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = "Cron.php";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Cron.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createCronJob(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = Utils::prompt('Nombre del CronJob', '/^[A-Z][a-zA-Z0-9_]*$/', 'empezar por mayúscula y sin espacios');
        if (empty($name)) {
            echo "* No introdujo el nombre del CronJob.\n";
            return;
        }

        $folder = 'CronJob/';
        $plugin = Utils::findPluginName();
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/CronJob.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
        if (file_exists('Cron.php')) {
            $this->updateCron($name);
        } else {
            $this->createCron($plugin);
            $this->updateCron($name);
        }
    }

    private function createExtensionAction(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $option = (int)Utils::prompt("Elija el tipo de extensión\n1=Tabla, 2=Modelo, 3=Controlador, 4=XMLView, 5=View");
        switch ($option) {
            case 1:
                $name = strtolower(Utils::prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
                $this->createExtensionTable($name);
                return;

            case 2:
                $name = Utils::prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionModel($name);
                return;

            case 3:
                $name = Utils::prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionController($name);
                return;

            case 4:
                $name = Utils::prompt('Nombre del XMLView', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionXMLView($name);
                return;

            case 5:
                $name = Utils::prompt('Nombre de la vista html.twig', '/^[a-zA-Z]+_[a-zA-Z]+_[0-9]+$/');
                $this->createExtensionView($name);
                return;
        }

        echo "* Opción no válida.\n";
    }

    private function createExtensionController(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre del controlador a extender.\n";
            return;
        }

        $folder = 'Extension/Controller/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del controlador " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/ExtensionController.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, Utils::getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

        $newContent = InitEditor::addToInitFunction('$this->loadExtension(new Extension\Controller\\' . $name . '());');
        if ($newContent) {
            InitEditor::setInitContent($newContent);
        }
    }

    private function createExtensionModel(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre del modelo a extender.\n";
            return;
        }

        $folder = 'Extension/Model/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/ExtensionModel.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, Utils::getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

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
            echo "* No introdujo el nombre de la tabla a extender.\n";
            return;
        }

        $folder = 'Extension/Table/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.xml';
        if (file_exists($fileName)) {
            echo "* La extensión de la tabla " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = Column::askMulti(true);
        FileGenerator::createTableXmlByFields($fileName, $name, $fields);
        echo '* ' . $fileName . self::OK;
    }

    private function createExtensionXMLView(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre del XMLView a extender.\n";
            return;
        }

        $folder = 'Extension/XMLView/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.xml';
        if (file_exists($fileName)) {
            echo "* El fichero " . $fileName . " YA EXISTE.\n";
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
        echo '* ' . $fileName . self::OK;
    }

    private function createExtensionView(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre de la vista a extender.\n";
            return;
        }

        $folder = 'Extension/View/';
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.html.twig';
        if (file_exists($fileName)) {
            echo "* El fichero " . $fileName . " YA EXISTE.\n";
            return;
        }

        file_put_contents($fileName, '');
        echo '* ' . $fileName . self::OK;
    }

    private function createInit(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = "Init.php";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Init.php.sample");
        $template = str_replace('[[NAME]]', Utils::findPluginName(), $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createModelAction(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = Utils::prompt(
            'Nombre del modelo (singular)',
            '/^[A-Z][a-zA-Z0-9_]*$/',
            'empezar por mayúscula y sin espacios'
        );
        if (empty($name)) {
            return;
        }

        $tableName = Utils::prompt(
            'Nombre de la tabla (plural)',
            '/^[a-z][a-z0-9_]*$/',
            'empezar por letra, todo en minúsculas y sin espacios'
        );
        if (empty($tableName)) {
            return;
        }

        $filePath = Utils::isCoreFolder() ? 'Core/Model/' : 'Model/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = Column::askMulti();
        FileGenerator::createModelByFields($fileName, $tableName, $fields, $name, Utils::getNamespace());
        echo '* ' . $fileName . self::OK;

        $tablePath = Utils::isCoreFolder() ? 'Core/Table/' : 'Table/';
        $tableFilename = $tablePath . $tableName . '.xml';
        Utils::createFolder($tablePath);
        if (false === file_exists($tableFilename)) {
            FileGenerator::createTableXmlByFields($tableFilename, $tableName, $fields);
            echo '* ' . $tableFilename . self::OK;
        } else {
            echo "\n" . '* ' . $tableFilename . " YA EXISTE";
        }

        echo "\n";
        if (Utils::prompt('¿Crear EditController? 0=No (predeterminado), 1=Si') === '1') {
            $this->createEditController($name, $fields);
        }

        echo "\n";
        if (Utils::prompt('¿Crear ListController? 0=No (predeterminado), 1=Si') === '1') {
            $this->createListController($name, $fields);
        }
    }

    private function createPluginAction(): void
    {
        if (file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
            echo "* No se puede crear un plugin en esta carpeta.\n";
            return;
        }

        // Estamos creando un Plugin, por lo que preguntaremos por el nombre de él
        $name = Utils::prompt('Nombre del plugin', '/^[A-Z][a-zA-Z0-9_]*$/', 'empezar por mayúscula y sin espacios');
        if (empty($name)) {
            echo "* El plugin debe tener un nombre.\n";
            return;
        } elseif (file_exists($name)) {
            echo "* El plugin " . $name . " YA EXISTE.\n";
            return;
        }

        mkdir($name, 0755);
        echo '* ' . $name . self::OK;

        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES',
            'Extension/Controller', 'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View',
            'Model/Join', 'Table', 'Translation', 'View', 'XMLView', 'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        foreach ($folders as $folder) {
            Utils::createFolder($name . '/' . $folder);
            touch($name . '/' . $folder . '/.gitignore');
        }

        UpdateTranslations::create($name);

        chdir($name);
        FileGenerator::createIni($name);
        FileGenerator::createGitIgnore();
        $this->createCron($name);
        $this->createInit();
    }

    private function createTestAction(): void
    {
        if (Utils::isCoreFolder() || false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = Utils::prompt(
            'Nombre del test (singular)',
            '/^[A-Z][a-zA-Z0-9_]*Test$/',
            'empezar por mayúscula y terminar en Test'
        );
        if (empty($name)) {
            echo "* No introdujo el nombre del test o está mal escrito.\n";
            return;
        }

        $filePath = 'Test/main/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El test " . $name . " YA EXISTE.\n";
            return;
        }

        $txtFile = $filePath . 'install-plugins.txt';
        if (false === file_exists($txtFile)) {
            // Creamos el fichero install-plugins.txt con el nombre del plugin
            $ini = parse_ini_file('facturascripts.ini');
            file_put_contents($txtFile, $ini['name']);
            echo '* ' . $txtFile . self::OK;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Test.php.sample");
        $nameSpace = Utils::getNamespace() . '\\' . str_replace('/', '\\', substr($filePath, 0, -1));
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$nameSpace, $name], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createWorkerAction(): void
    {
        if (false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = Utils::prompt(
            'Nombre del worker',
            '/^[A-Z][a-zA-Z0-9_]*$/',
            'empezar por mayúscula y sin espacios'
        );
        if (empty($name)) {
            return;
        }

        $filePath = 'Worker/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El worker " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Worker.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);

        echo '* ' . $fileName . self::OK;
        $input = Utils::prompt("¿Qué eventos debe escuchar el worker? 1=Insert, 2=Update, 3=Save, 4=Delete, 5=Todos, 6=Personalizado");
        $options = $input ? explode(' ', $input) : [];

        // comprobamos si se han introducido opciones
        if (count($options) === 0) {
            echo "* No se han introducido opciones.\n";
            return;
        }

        // si en las opciones esta algunos de los números del 1 al 5, preguntamos el modelo
        // y lo añadimos a la lista de opciones
        if (in_array(1, $options)
            || in_array(2, $options)
            || in_array(3, $options)
            || in_array(4, $options)
            || in_array(5, $options)) {
            $event = Utils::prompt('Introduce el nombre del modelo que contiene el evento a escuchar', '/^[A-Z][a-zA-Z0-9_]*$/');
        } elseif (in_array(6, $options)) {
            $event = Utils::prompt('Introduce el nombre del evento');
        } else {
            echo "* Error(Input): Opción no válida.\n";
            return;
        }

        // si el evento está vacío, no se ha introducido nada
        if (empty($event)) {
            echo "* El evento no puede estar vacío.\n";
            return;
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
                    echo "* Error(Input): Opción no válida.\n";
                    return;
            }

            if ($newContent) {
                InitEditor::setInitContent($newContent);
            }
        }
    }

    private function help(): void
    {
        echo 'FacturaScripts Maker v' . self::VERSION . "\n\n"
            . "crear:\n"
            . "$ fsmaker plugin\n"
            . "$ fsmaker api\n"
            . "$ fsmaker controller\n"
            . "$ fsmaker cron\n"
            . "$ fsmaker cronjob\n"
            . "$ fsmaker extension\n"
            . "$ fsmaker gitignore\n"
            . "$ fsmaker init\n"
            . "$ fsmaker model\n"
            . "$ fsmaker test\n"
            . "$ fsmaker upgrade\n"
            . "$ fsmaker upgrade-bs5\n"
            . "$ fsmaker worker\n\n"
            . "descargar:\n"
            . "$ fsmaker translations\n\n"
            . "ejecutar:\n"
            . "$ fsmaker run-tests [ruta carpeta FacturaScripts]\n\n"
            . "comprimir:\n"
            . "$ fsmaker zip\n\n";
    }

    private function updateCron(string $name): void
    {
        $fileStr = file_get_contents('Cron.php');
        $newJob = <<<END
        \n
                \$this->job($name::JOB_NAME)
                    ->everyDayAt(8)
                    ->run(function () {
                        $name::run();
                    });
        \n
        END;
        $search = 'public function run(): void';
        $position = strpos($fileStr, $search);
        $nameSpace = Utils::getNamespace();
        if ($position !== false) {
            $position = strpos($fileStr, '{', $position) + 1;
            $fileStr = substr_replace($fileStr, $newJob, $position, 0);
            file_put_contents('Cron.php', $fileStr);
            $usePosition = strpos($fileStr, 'use FacturaScripts\Core\Template\CronClass');
            $usePosition = strpos($fileStr, ';', $usePosition) + 1;
            $fileStr = substr_replace($fileStr, "\nuse FacturaScripts\\$nameSpace\\CronJob\\$name;", $usePosition, 0);
            file_put_contents('Cron.php', $fileStr);
            echo '* ' . 'Cron.php' . " actualizado con el nuevo CronJob.\n";
        } else {
            echo "* No se encontró el método run() en " . 'Cron.php' . ".\n";
        }
    }

    private function upgradeAction(): void
    {
        if (false === Utils::isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        FileUpdater::upgradePhpFiles();
        FileUpdater::upgradeXmlFiles();
        FileUpdater::upgradeTwigFiles();
    }
}

$argv = $_SERVER['argv'] ?? [];
new fsmaker($argv);
