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

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class fsmaker
{
    const VERSION = 1.9;
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

            case 'github-action':
                FileGenerator::createGithubAction();
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
                RunTests::run($argv[2] ?? '');
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

    private function createController($name): void
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
        } elseif (empty($name)) {
            Utils::echo("* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n");
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

        $sample = file_get_contents(__DIR__ . "/samples/Controller.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[MENU]]'], [Utils::getNamespace(), $name, $menu], $sample);
        Utils::createFolder($filePath);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);

        $viewPath = Utils::isCoreFolder() ? 'Core/View/' : 'View/';
        $viewFilename = $viewPath . $name . '.html.twig';
        Utils::createFolder($viewPath);
        if (file_exists($viewFilename)) {
            Utils::echo('* ' . $viewFilename . " YA EXISTE.\n");
            return;
        }

        $sample2 = file_get_contents(__DIR__ . "/samples/View.html.twig.sample");
        $template2 = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample2);
        file_put_contents($viewFilename, $template2);
        Utils::echo('* ' . $viewFilename . self::OK);
    }

    private function createControllerAction(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $option = select(
            label: 'Elija el tipo de controlador a crear',
            options: [
                // 'valor que devuelve' => 'key que se muestra al usuario a elegir'
                'Controller' => 'Controller',
                'ListController' => 'ListController',
                'EditController' => 'EditController'
            ],
            default: 'Controller',
            scroll: 3, // cantidad de opciones a mostrar a la vez en pantalla (el resto scroll)
            required: true
        );

        $modelName = Utils::promptStringWithRegex(
            label: "Nombre del $option",
            placeholder: 'Ej: Producto',
            hint: "El nombre del $option debe empezar por mayúscula y solo puede contener letras, números y guiones bajos, luego será colocado como 'List[Nombre elegido].php' por ejemplo.",
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        switch ($option) {
            case 'Controller':
                $this->createController($modelName);
                return;

            case 'ListController':
                $fields = Column::askMulti();
                $this->createListController($modelName, $fields);
                return;

            case 'EditController':
                $fields = Column::askMulti();
                $this->createEditController($modelName, $fields);
                return;
        }

        Utils::echo("Opción no válida.\n");
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
        } elseif (empty($modelName)) {
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

        $sample = file_get_contents(__DIR__ . "/samples/EditController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'edit');
        Utils::echo('* ' . $xmlFilename . self::OK);
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

        $title = text(
            label: 'Título',
            placeholder: 'Ej: Lista de Productos',
            default: '',
            required: true,
            validate: null,
            hint: 'El título que se colocará en "$data[\'title\'] = \'TÍTULO_ELEGIDO\';".'
        );

        $filePath = Utils::isCoreFolder() ? 'Core/Controller/' : 'Controller/';
        $fileName = $filePath . 'List' . $modelName . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El controlador " . $fileName . " YA EXISTE.\n");
            return;
        } elseif (empty($modelName)) {
            Utils::echo('* No introdujo el nombre del Controlador');
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/ListController.php.sample");
        $template = str_replace(
            ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]'],
            [Utils::getNamespace(), $modelName, $title, $menu],
            $sample
        );
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);

        $xmlPath = Utils::isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        Utils::createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            Utils::echo('* ' . $xmlFilename . " YA EXISTE\n");
            return;
        }

        FileGenerator::createXMLViewByFields($xmlFilename, $fields, 'list');
        Utils::echo('* ' . $xmlFilename . self::OK);
    }

    private function createCron(string $name): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $fileName = "Cron.php";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Cron.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
    }

    private function createCronJob(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $name = Utils::promptStringWithRegex(
            label: 'Nombre del CronJob',
            placeholder: 'Ej: MiCronJob',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $folder = 'CronJob/';
        $plugin = Utils::findPluginName();
        Utils::createFolder($folder);

        $fileName = $folder . $name . '.php';
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/CronJob.php.sample");
        $jobName = Utils::kebab($name);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]', '[[JOB_NAME]]'], [Utils::getNamespace(), $name, $jobName], $sample);

        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
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
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $option = (int)select(
            label: 'Elija el tipo de extensión',
            options: [
                // 'valor que devuelve' => 'key que se muestra al usuario a elegir'
                '1' => 'Tabla',
                '2' => 'Modelo',
                '3' => 'Controlador',
                '4' => 'XMLView',
                '5' => 'View'
            ],
            default: '1',
            scroll: 5, // cantidad de opciones a mostrar a la vez en pantalla (el resto scroll)
            required: true
        );

        switch ($option) {
            case 1:
                $name = Utils::promptStringWithRegex(
                    label: 'Nombre de la tabla (plural)',
                    placeholder: 'Ej: productos',
                    hint: 'El nombre de la tabla debe empezar por minúscula y solo puede contener minusculas, números y guiones bajos.',
                    regex: '/^[a-z][a-z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por minúscula y solo puede contener minusculas, números y guiones bajos.'
                );
                

                $this->createExtensionTable($name);
                return;

            case 2:
                $name = Utils::promptStringWithRegex(
                    label: 'Nombre del modelo (singular)',
                    placeholder: 'Ej: Producto',
                    hint: 'El nombre del modelo debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );

                $this->createExtensionModel($name);
                return;

            case 3:
                $name = Utils::promptStringWithRegex(
                    label: 'Nombre del controlador',
                    placeholder: 'Ej: ListFacturaCliente',
                    hint: 'El nombre del controlador debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );
                $this->createExtensionController($name);
                return;

            case 4:
                $name = Utils::promptStringWithRegex(
                    label: 'Nombre del XMLView',
                    placeholder: 'Ej: EditContacto',
                    hint: 'El nombre del XMLView debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
                    regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                    errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
                );
                $this->createExtensionXMLView($name);
                return;

            case 5:
                $name = Utils::promptStringWithRegex(
                    label: 'Nombre de la vista html.twig',
                    placeholder: 'Ej: factura_detalle_01',
                    hint: 'El nombre de la vista debe tener el formato: palabra_palabra_número (pudiendo ser mayuscula o minúscula).',
                    regex: '/^[a-zA-Z]+_[a-zA-Z]+_[0-9]+$/',
                    errorMessage: 'Inválido, debe tener el formato: palabra_palabra_número (pudiendo ser mayuscula o minúscula).'
                );
                $this->createExtensionView($name);
                return;
        }

        Utils::echo("* Opción no válida.\n");
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

        $sample = file_get_contents(__DIR__ . "/samples/ExtensionController.php.sample");
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

        $sample = file_get_contents(__DIR__ . "/samples/ExtensionModel.php.sample");
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
        Utils::echo('* ' . $fileName . self::OK);
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
        Utils::echo('* ' . $fileName . self::OK);
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
        Utils::echo('* ' . $fileName . self::OK);
    }

    private function createInit(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $fileName = "Init.php";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Init.php.sample");
        $template = str_replace('[[NAME]]', Utils::findPluginName(), $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
    }

    private function createModelAction(): void
    {
        if (false === Utils::isCoreFolder() && false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $name = Utils::promptStringWithRegex(
            label: 'Nombre del modelo (singular)',
            placeholder: 'Ej: Cliente',
            hint: 'El nombre debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $tableName = Utils::promptStringWithRegex(
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
            return;
        }

        $fields = Column::askMulti();
        FileGenerator::createModelByFields($fileName, $tableName, $fields, $name, Utils::getNamespace());
        Utils::echo('* ' . $fileName . self::OK);

        $tablePath = Utils::isCoreFolder() ? 'Core/Table/' : 'Table/';
        $tableFilename = $tablePath . $tableName . '.xml';
        Utils::createFolder($tablePath);
        if (false === file_exists($tableFilename)) {
            FileGenerator::createTableXmlByFields($tableFilename, $tableName, $fields);
            Utils::echo('* ' . $tableFilename . self::OK);
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
    }

    private function createPluginAction(): void
    {
        if (file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
            Utils::echo("* No se puede crear un plugin en esta carpeta.\n");
            return;
        }

        // Estamos creando un Plugin, por lo que preguntaremos por el nombre de él
        // promptear por el nombre del controlador y validar que sea un nombre válido
        $name = Utils::promptStringWithRegex(
            label: 'Nombre del plugin',
            placeholder: 'Ej: MiPlugin',
            hint: 'El nombre del plugin debe empezar por mayúscula, sin espacios y sin caracteres especiales.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
        );

        if (file_exists($name)) {
            Utils::echo("* El plugin " . $name . " YA EXISTE.\n");
            return;
        }

        mkdir($name, 0755);
        Utils::echo('* ' . $name . self::OK);

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
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $name = Utils::promptStringWithRegex(
            label: 'Nombre del test (singular)',
            placeholder: 'Ej: AccountingPlanTest',
            hint: 'El nombre del test debe empezar por mayúscula y terminar en Test',
            regex: '/^[A-Z][a-zA-Z0-9_]*Test$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y terminar en Test'
        );

        $filePath = 'Test/main/';
        $fileName = $filePath . $name . '.php';
        Utils::createFolder($filePath);
        if (file_exists($fileName)) {
            Utils::echo("* El test " . $name . " YA EXISTE.\n");
            return;
        }

        $txtFile = $filePath . 'install-plugins.txt';
        if (false === file_exists($txtFile)) {
            // Creamos el fichero install-plugins.txt con el nombre del plugin
            $ini = parse_ini_file('facturascripts.ini');
            file_put_contents($txtFile, $ini['name'] ?? '');
            Utils::echo('* ' . $txtFile . self::OK);
        }

        $sample = file_get_contents(__DIR__ . "/samples/Test.php.sample");
        $nameSpace = Utils::getNamespace() . '\\' . str_replace('/', '\\', substr($filePath, 0, -1));
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$nameSpace, $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
    }

    private function createWorkerAction(): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $name = Utils::promptStringWithRegex(
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
            return;
        }

        $sample = file_get_contents(__DIR__ . "/samples/Worker.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);

        Utils::echo('* ' . $fileName . self::OK);
        $options = multiselect(
            label: '¿Qué eventos debe escuchar el worker?',
            options: [
                // 'valor que devuelve' => 'key que se muestra al usuario a elegir'
                '1' => 'Insert',
                '2' => 'Update',
                '3' => 'Save',
                '4' => 'Delete',
                '5' => 'Todos',
                '6' => 'Personalizado'
            ],
            scroll: 6, // cantidad de opciones a mostrar a la vez en pantalla (el resto scroll)
            required: true
        );

        // si en las opciones esta algunos de los números del 1 al 5, preguntamos el modelo
        // y lo añadimos a la lista de opciones
        if (in_array(1, $options)
            || in_array(2, $options)
            || in_array(3, $options)
            || in_array(4, $options)
            || in_array(5, $options)) {
            $event = Utils::promptStringWithRegex(
                label: 'Introduce el nombre del modelo que contiene el evento a escuchar',
                placeholder: 'Ej: FacturaCliente',
                hint: 'El nombre debe empezar por mayúscula y contener solo texto, números o guiones bajos.',
                regex: '/^[A-Z][a-zA-Z0-9_]*$/',
                errorMessage: 'Inválido, debe empezar por mayúscula y contener solo texto, números o guiones bajos.'
            );
            } elseif (in_array(6, $options)) {
            $event = Utils::promptStringWithRegex(
                label: 'Introduce el nombre del evento',
                hint: 'El nombre debe contener solo texto, números o guiones.',
                regex: '/^[a-zA-Z0-9_-]*$/',
                errorMessage: 'Inválido, debe contener solo texto, números o guiones.'
            );
        } else {
            Utils::echo("* Error(Input): Opción no válida.\n");
            return;
        }

        // si el evento está vacío, no se ha introducido nada
        if (empty($event)) {
            Utils::echo("* El evento no puede estar vacío.\n");
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
                    Utils::echo("* Error(Input): Opción no válida.\n");
                    return;
            }

            if ($newContent) {
                InitEditor::setInitContent($newContent);
            }
        }
    }

    private function help(): void
    {
        Utils::echo('FacturaScripts Maker v' . self::VERSION . "\n\n"
            . "crear:\n"
            . "$ fsmaker plugin\n"
            . "$ fsmaker api\n"
            . "$ fsmaker controller\n"
            . "$ fsmaker cron\n"
            . "$ fsmaker cronjob\n"
            . "$ fsmaker extension\n"
            . "$ fsmaker github-action\n"
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
            . "$ fsmaker zip\n\n");
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
            Utils::echo('* ' . 'Cron.php' . " actualizado con el nuevo CronJob.\n");
        } else {
            Utils::echo("* No se encontró el método run() en " . 'Cron.php' . ".\n");
        }
    }

    private function upgradeAction(): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        FileUpdater::upgradePhpFiles();
        FileUpdater::upgradeXmlFiles();
        FileUpdater::upgradeTwigFiles();
        FileUpdater::upgradeIniFile();
    }
}

// Only auto-execute if this file is run directly, not when required by tests
if (!defined('PHPUNIT_COMPOSER_INSTALL') && !defined('__PHPUNIT_PHAR__') &&
    basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    $argv = $_SERVER['argv'] ?? [];
    new fsmaker($argv);
}
