<?php
/**
 * @author Carlos García Gómez            <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez       <hola@danielfg.es>
 * @author Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 */

if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

include __DIR__ . '/vendor/autoload.php';

use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\FileUpdater;

final class fsmaker
{
    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PA,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const VERSION = 1.4;
    const OK = " -> OK.\n";

    public function __construct($argv)
    {
        if (count($argv) < 2) {
            $this->help();
            return;
        }

        switch ($argv[1]) {
            case 'bs5':
                $this->bootstrap5Action();
                break;

            case 'controller':
                $this->createControllerAction();
                break;

            case 'cron':
                $name = $this->findPluginName();
                $this->createCron($name);
                break;
            case 'cronjob':
                $name = $this->findPluginName();
                $this->createCronjob($name);
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

            case 'test':
                $this->createTestAction();
                break;

            case 'translations':
                $this->updateTranslationsAction();
                break;

            case 'upgrade':
                $this->upgradeAction();
                break;

            case 'zip':
                $this->zipAction();
                break;

            default:
                $this->help();
                break;
        }
    }

    private function bootstrap5Action()
    {
        if (false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        FileUpdater::upgradeBootstrap5();
    }

    private function createController(): void
    {
        $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
        $filePath = $this->isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . $name . '.php';
        if (file_exists($fileName)) {
            echo "* El controlador " . $name . " YA EXISTE.\n";
            return;
        } elseif (empty($name)) {
            echo "* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Controller.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$this->getNamespace(), $name], $sample);
        $this->createFolder($filePath);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $viewPath = $this->isCoreFolder() ? 'Core/View/' : 'View/';
        $viewFilename = $viewPath . $name . '.html.twig';
        $this->createFolder($viewPath);
        if (file_exists($viewFilename)) {
            echo '* ' . $viewFilename . " YA EXISTE.\n";
            return;
        }

        $sample2 = file_get_contents(__DIR__ . "/SAMPLES/View.html.twig.sample");
        $template2 = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample2);
        file_put_contents($viewFilename, $template2);
        echo '* ' . $viewFilename . self::OK;
    }

    private function createControllerAction(): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $option = (int)$this->prompt("Elija el tipo de controlador a crear\n1=Controller, 2=ListController, 3=EditController");
        switch ($option) {
            case 1:
                $this->createController();
                return;

            case 2:
                $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $fields = Column::askMulti();
                $this->createListController($modelName, $fields);
                return;

            case 3:
                $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $fields = Column::askMulti();
                $this->createEditController($modelName, $fields);
                return;
        }

        echo "Opción no válida.\n";
    }

    private function createEditController(string $modelName, array $fields): void
    {
        $filePath = $this->isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'Edit' . $modelName . '.php';
        $this->createFolder($filePath);
        if (file_exists($fileName)) {
            echo "El controlador " . $fileName . " YA EXISTE.\n";
            return;
        } elseif (empty($modelName)) {
            return;
        }

        $menu = $this->prompt('Menú');
        $sample = file_get_contents(__DIR__ . "/SAMPLES/EditController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [$this->getNamespace(), $modelName, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = $this->isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        $this->createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'edit');
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createListController(string $modelName, array $fields): void
    {
        $menu = $this->prompt('Menú');
        $title = $this->prompt('Título');
        $filePath = $this->isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'List' . $modelName . '.php';
        $this->createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El controlador " . $fileName . " YA EXISTE.\n";
            return;
        } elseif (empty($modelName)) {
            echo '* No introdujo el nombre del Controlador';
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/ListController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]'],
            [$this->getNamespace(), $modelName, $title, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = $this->isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        $this->createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'list');
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createCron(string $name): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = "Cron.php";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Cron.php.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createCronJob(string $name): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $folder = 'CronJob/';
        $this->createFolder($folder);

        $fileName = $folder . "CronJob.php";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/CronJob.php.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createExtensionAction(): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $option = (int)$this->prompt("Elija el tipo de extensión\n1=Tabla, 2=Modelo, 3=Controlador, 4=XMLView, 5=View");
        switch ($option) {
            case 1:
                $name = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
                $this->createExtensionTable($name);
                return;

            case 2:
                $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionModel($name);
                return;

            case 3:
                $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionController($name);
                return;

            case 4:
                $name = $this->prompt('Nombre del XMLView', '/^[A-Z][a-zA-Z0-9_]*$/');
                $this->createExtensionXMLView($name);
                return;

            case 5:
                $name = $this->prompt('Nombre de la vista html.twig', '/^[a-zA-Z]+_[a-zA-Z]+_[0-9]+$/');
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
        $this->createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del controlador " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/ExtensionController.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

        $this->modifyInit($name, 1);
    }

    private function createExtensionModel(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre del modelo a extender.\n";
            return;
        }

        $folder = 'Extension/Model/';
        $this->createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/ExtensionModel.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

        $this->modifyInit($name, 0);
    }

    private function createExtensionTable(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre de la tabla a extender.\n";
            return;
        }

        $folder = 'Extension/Table/';
        $this->createFolder($folder);

        $fileName = $folder . $name . '.xml';
        if (file_exists($fileName)) {
            echo "* La extensión de la tabla " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = Column::askMulti(true);
        FileGenerator::createTableXmlByFields($fileName, $name, $fields, true);
        echo '* ' . $fileName . self::OK;
    }

    private function createExtensionXMLView(string $name): void
    {
        if (empty($name)) {
            echo "* No introdujo el nombre del XMLView a extender.\n";
            return;
        }

        $folder = 'Extension/XMLView/';
        $this->createFolder($folder);

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
        $this->createFolder($folder);

        $fileName = $folder . $name . '.html.twig';
        if (file_exists($fileName)) {
            echo "* El fichero " . $fileName . " YA EXISTE.\n";
            return;
        }

        file_put_contents($fileName, '');
        echo '* ' . $fileName . self::OK;
    }

    private function createFolder(string $path): void
    {
        if (file_exists($path)) {
            return;
        }

        if (mkdir($path, 0755, true)) {
            echo '* ' . $path . self::OK;
        }
    }

    private function createInit(): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = "Init.php";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Init.php.sample");
        $template = str_replace('[[NAME]]', $this->findPluginName(), $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createModelAction(): void
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/', 'empezar por mayúscula y sin espacios');
        if (empty($name)) {
            return;
        }

        $tableName = $this->prompt('Nombre de la tabla (plural)', '/^[a-z][a-z0-9_]*$/', 'empezar por letra, todo en minúsculas y sin espacios');
        if (empty($tableName)) {
            return;
        }

        $filePath = $this->isCoreFolder() ? 'Core/Model/' : 'Model/';
        $fileName = $filePath . $name . '.php';
        $this->createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = Column::askMulti();
        FileGenerator::createModelByFields($fileName, $tableName, $fields, $name, $this->getNamespace());
        echo '* ' . $fileName . self::OK;

        $tablePath = $this->isCoreFolder() ? 'Core/Table/' : 'Table/';
        $tableFilename = $tablePath . $tableName . '.xml';
        $this->createFolder($tablePath);
        if (false === file_exists($tableFilename)) {
            FileGenerator::createTableXmlByFields($tableFilename, $tableName, $fields);
            echo '* ' . $tableFilename . self::OK;
        } else {
            echo "\n" . '* ' . $tableFilename . " YA EXISTE";
        }

        echo "\n";
        if ($this->prompt('¿Crear EditController? 0=No (predeterminado), 1=Si') === '1') {
            $this->createEditController($name, $fields);
        }

        echo "\n";
        if ($this->prompt('¿Crear ListController? 0=No (predeterminado), 1=Si') === '1') {
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
        $name = $this->prompt('Nombre del plugin', '/^[A-Z][a-zA-Z0-9_]*$/', 'empezar por mayúscula y sin espacios');
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
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES', 'Extension/Controller',
            'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View', 'Model/Join', 'Table', 'Translation', 'View', 'XMLView',
            'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        foreach ($folders as $folder) {
            $this->createFolder($name . '/' . $folder);
            touch($name . '/' . $folder . '/.gitignore');
        }

        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            file_put_contents(
                $name . '/Translation/' . $filename . '.json',
                '{"' . strtolower($name) . '": "' . $name . '"}'
            );
            echo '* ' . $name . '/Translation/' . $filename . ".json" . self::OK;
        }

        chdir($name);
        FileGenerator::createIni($name);
        FileGenerator::createGitIgnore();
        $this->createCron($name);
        $this->createInit();
    }

    private function createTestAction(): void
    {
        if ($this->isCoreFolder() || false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = $this->prompt('Nombre del test (singular)', '/^[A-Z][a-zA-Z0-9_]*Test$/', 'empezar por mayúscula y terminar en Test');
        if (empty($name)) {
            echo "* No introdujo el nombre del test o está mal escrito.\n";
            return;
        }

        $filePath = 'Test/main/';
        $fileName = $filePath . $name . '.php';
        $this->createFolder($filePath);
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

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Test.php.sample");
        $nameSpace = $this->getNamespace() . '\\' . str_replace('/', '\\', substr($filePath, 0, -1));
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$nameSpace, $name], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function findPluginName(): string
    {
        if ($this->isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return $ini['name'] ?? '';
        }

        return '';
    }

    private function getNamespace(): string
    {
        if ($this->isCoreFolder()) {
            return 'Core';
        }

        $ini = parse_ini_file('facturascripts.ini');
        return 'Plugins\\' . $ini['name'];
    }

    private function help(): void
    {
        echo 'FacturaScripts Maker v' . self::VERSION . "\n\n"
            . "crear:\n"
            . "$ fsmaker plugin\n"
            . "$ fsmaker model\n"
            . "$ fsmaker controller\n"
            . "$ fsmaker extension\n"
            . "$ fsmaker gitignore\n"
            . "$ fsmaker cron\n"
            . "$ fsmaker cronjob\n"
            . "$ fsmaker init\n"
            . "$ fsmaker test\n"
            . "$ fsmaker upgrade\n"
            . "$ fsmaker bs5\n\n"
            . "descargar:\n"
            . "$ fsmaker translations\n\n"
            . "comprimir:\n"
            . "$ fsmaker zip\n";
    }

    private function isCoreFolder(): bool
    {
        return file_exists('Core/Translation') && false === file_exists('facturascripts.ini');
    }

    private function isPluginFolder(): bool
    {
        return file_exists('facturascripts.ini');
    }

    private function modifyInit(string $name, int $modelOrController): void
    {
        $fileName = "Init.php";
        if (false === file_exists($fileName)) {
            $this->createInit();
        }

        $fileStr = file_get_contents($fileName);
        $toSearch = '// se ejecuta cada vez que carga FacturaScripts (si este plugin está activado).';
        $toChange = $toSearch . "\n" . '        $this->loadExtension(new Extension\Controller\\' . $name . '());';
        if ($modelOrController === 0) {
            $toChange = $toSearch . "\n" . '        $this->loadExtension(new Extension\Model\\' . $name . '());';
        }

        $newFileStr = str_replace($toSearch, $toChange, $fileStr);
        file_put_contents($fileName, $newFileStr);
        echo '* ' . $fileName . self::OK;
    }

    private function prompt(string $label, string $pattern = '', string $pattern_explain = ''): ?string
    {
        echo $label . ': ';
        $matches = [];
        $value = trim(fgets(STDIN));

        // si el valor esta vacío, devolvemos null
        if ($value == '') {
            return null;
        }

        if (!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            echo "Valor no válido. Debe " . $pattern_explain . "\n";
            return '';
        }

        return $value;
    }

    private function updateTranslationsAction(): void
    {
        if ($this->isPluginFolder()) {
            $folder = 'Translation/';
            $this->createFolder($folder);
            $ini = parse_ini_file('facturascripts.ini');
            $project = $ini['name'] ?? '';
        } elseif ($this->isCoreFolder()) {
            $folder = 'Core/Translation/';
            $this->createFolder($folder);
            $project = 'CORE';
        } else {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        if (empty($project)) {
            echo "Proyecto desconocido.\n";
            return;
        }

        // download json from facturascripts.com
        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            echo "D " . $folder . $filename . ".json";
            $url = "https://facturascripts.com/EditLanguage?action=json&project=" . $project . "&code=" . $filename;
            $json = file_get_contents($url);
            if (!empty($json) && strlen($json) > 10) {
                file_put_contents($folder . $filename . '.json', $json);
                echo "\n";
                continue;
            }

            echo " - vacío\n";
        }
    }

    private function upgradeAction(): void
    {
        if (false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        FileUpdater::upgradePhpFiles();
        FileUpdater::upgradeXmlFiles();
        FileUpdater::upgradeTwigFiles();
    }

    private function zipAction(): void
    {
        if (false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $ini = parse_ini_file('facturascripts.ini');
        $pluginName = $ini['name'] ?? '';
        if (empty($pluginName)) {
            echo "* No se ha encontrado el nombre del plugin.\n";
            return;
        }

        $customName = $this->prompt("¿Cuál es el nombre del zip?, dejar en blanco para usar el nombre del plugin.\n");
        if (empty($customName)) {
            $zipName = $pluginName . '.zip';
        } else {
            $zipName = $customName . '.zip';
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($zipName, ZipArchive::CREATE)) {
            echo "* Error al crear el archivo zip.\n";
            return;
        }

        $zip->addEmptyDir($pluginName);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.'),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (
                $file->getFilename() === '.' ||
                $file->getFilename() === '..' ||
                $file->getFilename()[0] === '.' ||
                substr($name, 0, 3) === './.'
            ) {
                continue;
            }
            $path = str_replace('./', $pluginName . '/', $name);
            $zip->addFile($name, $path);
        }

        $zip->close();
        echo "* " . $zipName . self::OK;
    }
}

$argv = $_SERVER['argv'] ?? [];
new fsmaker($argv);
