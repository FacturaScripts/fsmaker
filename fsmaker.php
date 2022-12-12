<?php

/**
 * @author Carlos García Gómez            <carlos@facturascripts.com>
 * @author Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 */
if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

final class fsmaker
{

    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PA,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const FORBIDDEN_WORDS = 'action,activetab,code';
    const VERSION = 1.21;
    const OK = " -> OK.\n";

    public function __construct($argv)
    {
        if (count($argv) < 2) {
            $this->help();
            return;
        }

        $name = $this->findPluginName();
        switch ($argv[1]) {
            case 'controller':
                $this->createControllerAction();
                break;

            case 'cron':
                $this->createCron($name);
                break;

            case 'extension':
                $this->createExtensionAction();
                break;

            case 'gitignore':
                $this->createGitIgnore();
                break;

            case 'init':
                $this->createInit($name);
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

            case 'zip':
                $this->zipAction();
                break;

            default:
                $this->help();
                break;
        }
    }

    private function askFields(): array
    {
        $fields = [];

        echo "\n";
        if ($this->prompt("¿Desea crear los campos (id, creationdate, lastupdate, nick, lastnick, name) por defecto? 1=Si, 0=No\n") === '1') {
            $fields = [
                'id' => 'serial',
                'creationdate' => 'timestamp',
                'lastupdate' => 'timestamp',
                'nick' => 'character varying(50)',
                'lastnick' => 'character varying(50)',
                'name' => 'character varying(100)'
            ];
        }
        echo "\n";

        while (true) {
            $name = strtolower($this->prompt('Nombre del campo (vacío para terminar)'));
            if (empty($name)) {
                break;
            }

            if (in_array($name, explode(',', self::FORBIDDEN_WORDS))) {
                echo "\n" . self::FORBIDDEN_WORDS . " son nombres no permitidos.\n";
                continue;
            }

            $type = $this->askType(false);
            switch ($type) {
                case 1:
                    $clave = array_search('serial', $fields);
                    if ($clave !== false) {
                        unset($fields[$clave]);
                    }
                    $fields[$name] = 'serial';
                    break;

                case 2:
                    $fields[$name] = 'integer';
                    break;

                case 3:
                    $fields[$name] = 'double precision';
                    break;

                case 4:
                    $fields[$name] = 'boolean';
                    break;

                case 5:
                    $long = (int)$this->prompt("\nLongitud caracteres");
                    $fields[$name] = "character varying($long)";
                    break;

                case 6:
                    $fields[$name] = 'text';
                    break;

                case 7:
                    $fields[$name] = 'timestamp';
                    break;

                case 8:
                    $fields[$name] = 'date';
                    break;

                case 9:
                    $fields[$name] = 'time';
                    break;
            }

            echo "\n";
        }

        return $fields;
    }

    private function askType(bool $serial): int
    {
        while (true) {
            echo "\n";
            $type = (int)$this->prompt("Elija el tipo de campo\n"
                . "1 = serial (autonumérico, ideal para ids)\n"
                . "2 = integer\n"
                . "3 = float\n"
                . "4 = boolean\n"
                . "5 = character varying\n"
                . "6 = text\n"
                . "7 = timestamp\n"
                . "8 = date\n"
                . "9 = time\n");
            if ($type === 1 && $serial) {
                echo "\nYa hay un campo de tipo serial.\n";
                continue;
            }

            if ($type >= 1 && $type <= 9) {
                return $type;
            }

            echo "\nOpción incorrecta.\n";
        }
    }

    private function createController()
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

    private function createControllerAction()
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
                $fields = $this->askFields();
                $this->createControllerList($modelName, $fields);
                return;

            case 3:
                $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $fields = $this->askFields();
                $this->createControllerEdit($modelName, $fields);
                return;
        }

        echo "Opción no válida.\n";
    }

    private function createControllerEdit(string $modelName, array $fields)
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

        $sample = file_get_contents(__DIR__ . "/SAMPLES/EditController.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[MODEL_NAME]]'], [$this->getNamespace(), $modelName], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = $this->isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'Edit' . $modelName . '.xml';
        $this->createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        $this->createXMLViewByFields($xmlFilename, $fields, 1);
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createControllerList(string $modelName, array $fields)
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
        $template = str_replace(['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]'],
            [$this->getNamespace(), $modelName, $title, $menu],
            $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlPath = $this->isCoreFolder() ? 'Core/XMLView/' : 'XMLView/';
        $xmlFilename = $xmlPath . 'List' . $modelName . '.xml';
        $this->createFolder($xmlPath);
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        $this->createXMLViewByFields($xmlFilename, $fields, 0);
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createCron(string $name)
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

    private function createExtensionAction()
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $option = (int)$this->prompt("Elija el tipo de extensión\n1=Tabla, 2=Modelo, 3=Controlador, 4=XMLView");
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
        }

        echo "* Opción no válida.\n";
    }

    private function createExtensionController(string $name)
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

    private function createExtensionModel(string $name)
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

    private function createExtensionTable(string $name)
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

        $fields = $this->askFields();
        $this->createXMLTableByFields($fileName, $name, $fields);
        echo '* ' . $fileName . self::OK;
    }

    private function createExtensionXMLView(string $name)
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

        $fields = $this->askFields();
        $this->createXMLViewByFields($fileName, $fields, 2);
        echo '* ' . $fileName . self::OK;
    }

    private function createFolder(string $path)
    {
        if (file_exists($path)) {
            return;
        }

        if (mkdir($path, 0755, true)) {
            echo '* ' . $path . self::OK;
        }
    }

    private function createGitIgnore()
    {
        $fileName = '.gitignore';
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $template = file_get_contents(__DIR__ . "/SAMPLES/gitignore.sample");
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createIni(string $name)
    {
        $fileName = "facturascripts.ini";
        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/facturascripts.ini.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createInit(string $name)
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
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createModelAction()
    {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
        $tableName = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
        if (empty($name) || empty($tableName)) {
            echo "* No introdujo ni el modelo ni la tabla.\n";
            return;
        }

        $filePath = $this->isCoreFolder() ? 'Core/Model/' : 'Model/';
        $fileName = $filePath . $name . '.php';
        $this->createFolder($filePath);
        if (file_exists($fileName)) {
            echo "* El modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = $this->askFields();
        $this->createModelByFields($fileName, $tableName, $fields, $name);
        echo '* ' . $fileName . self::OK;

        $tablePath = $this->isCoreFolder() ? 'Core/Table/' : 'Table/';
        $tableFilename = $tablePath . $tableName . '.xml';
        $this->createFolder($tablePath);
        if (false === file_exists($tableFilename)) {
            $this->createXMLTableByFields($tableFilename, $tableName, $fields);
            echo '* ' . $tableFilename . self::OK;
        } else {
            echo "\n" . '* ' . $tableFilename . " YA EXISTE";
        }

        echo "\n";
        if ($this->prompt('¿Crear EditController? 1=Si, 0=No') === '1') {
            $this->createControllerEdit($name, $fields);
        }

        echo "\n";
        if ($this->prompt('¿Crear ListController? 1=Si, 0=No') === '1') {
            $this->createControllerList($name, $fields);
        }
    }

    private function createModelByFields(string $fileName, string $tableName, array $fields, string $name)
    {
        $properties = '';
        $primaryColumn = '';
        $clear = '';
        $clearExclude = ['lastupdate', 'lastnick'];
        $test = '';

        $creationdate = array_key_exists('creationdate', $fields);
        $lastupdate = array_key_exists('lastupdate', $fields);
        $nick = array_key_exists('nick', $fields);
        $lastnick = array_key_exists('lastnick', $fields);

        foreach ($fields as $property => $type) {
            // Para la creación de la primaryColumn
            if ($type === 'serial') {
                $primaryColumn = $property;
            }

            // para el especificar el tipo de propiedad
            $typeProperty = '';

            // Para el método clear()
            switch ($type) {
                case 'serial':
                    $typeProperty = 'int';
                    $primaryColumn = $property;
                    break;

                case 'integer':
                    $typeProperty = 'int';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = 0;' . "\n";
                    }
                    break;

                case 'double precision':
                    $typeProperty = 'float';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = 0.0;' . "\n";
                    }
                    break;

                case 'boolean':
                    $typeProperty = 'bool';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = false;' . "\n";
                    }
                    break;

                case 'timestamp':
                    $typeProperty = 'string';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = date(self::DATETIME_STYLE);' . "\n";
                    }
                    break;

                case 'date':
                    $typeProperty = 'string';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = date(self::DATE_STYLE);' . "\n";
                    }
                    break;

                case 'time':
                    $typeProperty = 'string';
                    if (false === in_array($property, $clearExclude)) {
                        $clear .= '        $this->' . $property . ' = date(self::TIME_STYLE);' . "\n";
                    }
                    break;

                case 'text':
                    $typeProperty = 'string';
                    $test .= '        $this->' . $property . ' = $this->toolBox()->utils()->noHtml($this->' . $property . ');' . "\n";
                    break;
            }

            if ($property === 'nick') {
                $clear .= '        $this->nick = Session::get(\'user\')->nick ?? null;' . "\n";
            }

            if (strpos($type, 'character varying') !== false) {
                $typeProperty = 'string';
                $test .= '        $this->' . $property . ' = $this->toolBox()->utils()->noHtml($this->' . $property . ');' . "\n";
            }

            // Para la creación de properties
            $properties .= "    /** @var " . $typeProperty . " */\n";
            $properties .= "    public $" . $property . ";" . "\n\n";
        }

        $sample = '<?php' . "\n"
            . 'namespace FacturaScripts\\' . $this->getNamespace() . '\Model;' . "\n\n"
            . "use FacturaScripts\Core\Model\Base\ModelClass;\n"
            . "use FacturaScripts\Core\Model\Base\ModelTrait;\n";

        if ($creationdate || $lastupdate || $nick || $lastnick) {
            $sample .= "use FacturaScripts\Core\Session;\n\n";
        }

        $sample .= 'class ' . $name . ' extends ModelClass' . "\n"
            . '{' . "\n"
            . '    use ModelTrait;' . "\n\n"
            . $properties
            . "    public function clear() \n"
            . "    {\n"
            . '        parent::clear();' . "\n"
            . $clear
            . '    }' . "\n\n"
            . "    public static function primaryColumn(): string\n"
            . "    {\n"
            . '        return "' . $primaryColumn . '";' . "\n"
            . '    }' . "\n\n"
            . "    public static function tableName(): string\n"
            . "    {\n"
            . '        return "' . $tableName . '";' . "\n"
            . '    }' . "\n\n"
            . "    public function test(): bool\n"
            . "    {\n"
            . $test
            . '        return parent::test();' . "\n"
            . '    }' . "\n\n";

        if ($lastupdate || $lastnick) {
            $sample .= '    protected function saveInsert(array $values = []): bool' . "\n"
                . "    {\n";
        }

        if ($lastupdate) {
            $sample .= '        $this->lastupdate = null;' . "\n";
        }

        if ($lastnick) {
            $sample .= '        $this->lastnick = null;' . "\n";
        }

        if ($lastupdate || $lastnick) {
            $sample .= '        return parent::saveInsert($values);' . "\n"
                . '    }' . "\n\n";
        }

        if ($lastupdate || $lastnick) {
            $sample .= '    protected function saveUpdate(array $values = []): bool' . "\n"
                . "    {\n";
        }

        if ($lastupdate) {
            $sample .= '        $this->lastupdate = date(self::DATETIME_STYLE);' . "\n";
        }

        if ($lastnick) {
            $sample .= '        $this->lastnick = Session::get(\'user\')->nick ?? null;' . "\n";
        }

        if ($lastupdate || $lastnick) {
            $sample .= '        return parent::saveUpdate($values);' . "\n"
                . '    }' . "\n";
        }

        $sample .=  '}' . "\n";
        file_put_contents($fileName, $sample);
    }

    private function createPluginAction()
    {
        if (file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
            echo "* No se puede crear un plugin en esta carpeta.\n";
            return;
        }

        // Estamos creando un Plugin, por lo que preguntaremos por el nombre de él
        $name = $this->prompt('Nombre del plugin', '/^[A-Z][a-zA-Z0-9_]*$/');
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
            'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Model/Join', 'Table', 'Translation', 'View', 'XMLView',
            'Test/main'
        ];
        foreach ($folders as $folder) {
            $this->createFolder($name . '/' . $folder);
        }

        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            file_put_contents(
                $name . '/Translation/' . $filename . '.json',
                '{"' . strtolower($name) . '": "' . $name . '"}'
            );
            echo '* ' . $name . '/Translation/' . $filename . ".json" . self::OK;
        }

        chdir($name);
        $this->createIni($name);
        $this->createGitIgnore();
        $this->createCron($name);
        $this->createInit($name);
    }

    private function createTestAction()
    {
        if ($this->isCoreFolder() || false === $this->isPluginFolder()) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $name = $this->prompt('Nombre del test (singular)', '/^[A-Z][a-zA-Z0-9_]*Test$/');
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
        $ini = parse_ini_file('facturascripts.ini');
        file_put_contents($txtFile, $ini['name']);
        echo '* ' . $txtFile . self::OK;

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Test.php.sample");
        $nameSpace = $this->getNamespace() . '\\' . str_replace('/', '\\', substr($filePath, 0, -1));
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [$nameSpace, $name], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createXMLTableByFields(string $tableFilename, string $tableName, array $fields)
    {
        $columns = '';
        $constraints = '';
        foreach ($fields as $key => $type) {
            $columns .= "    <column>\n"
                . "        <name>$key</name>\n"
                . "        <type>$type</type>\n";

            if ($type === 'serial') {
                $columns .= "        <null>NO</null>\n";
            }

            $columns .= "    </column>\n";

            if ($type === 'serial') {
                $constraints .= "    <constraint>\n"
                    . '        <name>' . $tableName . "_pkey</name>\n"
                    . '        <type>PRIMARY KEY (' . $key . ")</type>\n"
                    . "    </constraint>\n";
            }

            if ($key === 'nick' || $key === 'lastnick') {
                $constraints .= "    <constraint>\n"
                    . "        <name>ca_" . $tableName . "_users_" . $key . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $key . ") REFERENCES users (nick) ON DELETE SET NULL ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";
            }
        }

        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<table>' . "\n"
            . $columns
            . $constraints
            . '</table>' . "\n";
        file_put_contents($tableFilename, $sample);
    }

    private function createXMLViewByFields(string $xmlFilename, array $fields, int $editOrList)
    {
        if (empty($fields)) {
            $fields = $this->askFields();
        }

        // Creamos el xml con los campos introducidos
        $groupName = 'data';
        if ($editOrList === 2) { // Es una extension
            $groupName = 'data_extension';
        }

        $tabForColums = 12;
        if ($editOrList === 0) { // Es un ListController
            $tabForColums = 8;
        }

        $order = 100;
        $columns = '';

        foreach ($fields as $key => $type) {
            $columns .= $this->getWidget($key, $type, $order, $tabForColums);
            $order += 10;
        }

        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<view>' . "\n"
            . '    <columns>' . "\n";

        switch ($editOrList) {
            case 0: // Es un ListController
                $sample .= $columns;
                break;

            case 1: // Es un EditController
            case 2: // Es una extensión
                $sample .= '        <group name="' . $groupName . '" numcolumns="12">' . "\n"
                    . $columns
                    . '        </group>' . "\n";
                break;

            default: // No es ninguna de las opciones de antes
                return;
        }

        $sample .= '    </columns>' . "\n"
            . '</view>' . "\n";

        file_put_contents($xmlFilename, $sample);
    }

    private function findPluginName(): string
    {
        if ($this->isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return $ini['name'] ?? '';
        }

        return '';
    }

    private function getWidget(string $name, string $type, string $order, int $tabForColums): string
    {
        $spaces = str_repeat(" ", $tabForColums);
        $sample = '';

        switch ($type) {
            default:
                $sample .= $spaces . '<column name="' . $name . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $name . '" />' . "\n";
                break;

            case 'serial':
                $sample .= $spaces . '<column name="' . $name . '" display="none" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $name . '" />' . "\n";
                break;

            case 'double precision':
            case 'int':
                $sample .= $spaces . '<column name="' . $name . '" display="right" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="number" fieldname="' . $name . '" />' . "\n";
                break;

            case 'boolean':
                $sample .= $spaces . '<column name="' . $name . '" display="center" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="checkbox" fieldname="' . $name . '" />' . "\n";
                break;

            case 'text':
                $sample .= $spaces . '<column name="' . $name . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="textarea" fieldname="' . $name . '" />' . "\n";
                break;

            case 'timestamp':
                $sample .= $spaces . '<column name="' . $name . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="datetime" fieldname="' . $name . '" />' . "\n";
                break;

            case 'date':
                $sample .= $spaces . '<column name="' . $name . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="date" fieldname="' . $name . '" />' . "\n";
                break;

            case 'time':
                $sample .= $spaces . '<column name="' . $name . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="time" fieldname="' . $name . '" />' . "\n";
                break;
        }

        $sample .= $spaces . "</column>\n";
        return $sample;
    }

    private function getNamespace(): string
    {
        if ($this->isCoreFolder()) {
            return 'Core';
        }

        $ini = parse_ini_file('facturascripts.ini');
        return 'Plugins\\' . $ini['name'];
    }

    private function help()
    {
        echo 'FacturaScripts Maker v' . self::VERSION . "\n\n"
            . "crear:\n"
            . "$ fsmaker plugin\n"
            . "$ fsmaker model\n"
            . "$ fsmaker controller\n"
            . "$ fsmaker extension\n"
            . "$ fsmaker gitignore\n"
            . "$ fsmaker cron\n"
            . "$ fsmaker init\n"
            . "$ fsmaker test\n\n"
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

    private function modifyInit(string $name, int $modelOrController)
    {
        $fileName = "Init.php";
        if (false === file_exists($fileName)) {
            $this->createInit($name);
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

    private function prompt($label, $pattern = ''): string
    {
        echo $label . ': ';
        $matches = [];
        $value = trim(fgets(STDIN));
        if (!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            echo "Valor no válido. Debe cumplir: " . $pattern . "\n";
            return '';
        }

        return $value;
    }

    private function updateTranslationsAction()
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

    private function zipAction()
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
            if ($file->getFilename() === '.'
                || $file->getFilename() === '..'
                || $file->getFilename()[0] === '.'
                || substr($name, 0, 3) === './.') {
                continue;
            }
            $path = str_replace('./', $pluginName . '/', $name);
            $zip->addFile($name, $path);
        }

        $zip->close();
        echo "* " . $zipName . self::OK;
    }
}

new fsmaker($argv);
