<?php
/**
 * @author Carlos García Gómez            <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez       <hola@danielfg.es>
 * @author Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 */
if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

include __DIR__ . '/Columna.php';

final class fsmaker
{
    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PA,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const FORBIDDEN_WORDS = 'action,activetab,code';
    const VERSION = 1.29;
    const OK = " -> OK.\n";

    public $globalFields = false;

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
        $this->globalFields = false;

        echo "\n";
        if ($this->prompt("¿Desea crear los campos (id, creationdate, lastupdate, nick, lastnick, name) por defecto? 1=Si, 0=No\n") === '1') {
            $this->globalFields = true;
            $fields[] = new Columna([
                'display' => 'none',
                'nombre' => 'id',
                'requerido' => true,
                'tipo' => 'serial'
            ]);
            $fields[] = new Columna([
                'display' => 'none',
                'nombre' => 'creationdate',
                'requerido' => true,
                'tipo' => 'timestamp'
            ]);
            $fields[] = new Columna([
                'display' => 'none',
                'nombre' => 'lastupdate',
                'tipo' => 'timestamp'
            ]);
            $fields[] = new Columna([
                'display' => 'none',
                'nombre' => 'nick',
                'tipo' => 'character varying',
                'longitud' => 50
            ]);
            $fields[] = new Columna([
                'display' => 'none',
                'nombre' => 'lastnick',
                'tipo' => 'character varying',
                'longitud' => 50
            ]);
            $fields[] = new Columna([
                'nombre' => 'name',
                'tipo' => 'character varying',
                'longitud' => 100
            ]);
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

            $column = new Columna(['nombre' => $name]);
            $column->ask($fields);

            $fields[] = $column;
            echo "\n";
        }

        // ordenamos el array por la propiedad nombre
        usort($fields, function ($a, $b) {
            return strcmp($a->nombre, $b->nombre);
        });

        return $fields;
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

    private function createExtensionView(string $name)
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

    private function createInit()
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
        $clearExclude = ['creationdate', 'nick', 'lastnick', 'lastupdate'];
        $test = '';
        $testExclude = ['creationdate', 'nick', 'lastnick', 'lastupdate'];

        foreach ($fields as $field) {
            // para especificar el tipo de propiedad
            $typeProperty = '';

            // Para el método clear()
            switch ($field->tipo) {
                case 'serial':
                    $typeProperty = 'int';
                    $primaryColumn = $field->nombre;
                    break;

                case 'integer':
                    $typeProperty = 'int';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = 0;' . "\n";
                    }
                    break;

                case 'double precision':
                    $typeProperty = 'float';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = 0.0;' . "\n";
                    }
                    break;

                case 'boolean':
                    $typeProperty = 'bool';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = false;' . "\n";
                    }
                    break;

                case 'timestamp':
                    $typeProperty = 'string';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = date(self::DATETIME_STYLE);' . "\n";
                    }
                    break;

                case 'date':
                    $typeProperty = 'string';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = date(self::DATE_STYLE);' . "\n";
                    }
                    break;

                case 'time':
                    $typeProperty = 'string';
                    if (false === in_array($field->nombre, $clearExclude)) {
                        $clear .= '        $this->' . $field->nombre . ' = date(self::HOUR_STYLE);' . "\n";
                    }
                    break;

                case 'text':
                    $typeProperty = 'string';
                    if (false === in_array($field->nombre, $testExclude)) {
                        $test .= '        $this->' . $field->nombre . ' = $this->toolBox()->utils()->noHtml($this->' . $field->nombre . ');' . "\n";
                    }
                    break;
            }

            if (strpos($field->tipo, 'character varying') !== false) {
                $typeProperty = 'string';
                if (false === in_array($field->nombre, $testExclude)) {
                    $test .= '        $this->' . $field->nombre . ' = $this->toolBox()->utils()->noHtml($this->' . $field->nombre . ');' . "\n";
                }
            }

            // Para la creación de properties
            $properties .= "    /** @var " . $typeProperty . " */\n";
            $properties .= "    public $" . $field->nombre . ";" . "\n\n";
        }

        $sample = '<?php' . "\n"
            . 'namespace FacturaScripts\\' . $this->getNamespace() . '\Model;' . "\n\n"
            . "use FacturaScripts\Core\Model\Base\ModelClass;\n"
            . "use FacturaScripts\Core\Model\Base\ModelTrait;\n";

        if ($this->globalFields) {
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
            . "    {\n";

        if ($this->globalFields) {
            $sample .= '        if ($this->exists()) {' . "\n"
                . '            $this->lastnick = Session::get(\'user\') ? Session::get(\'user\')->nick : null;' . "\n"
                . '            $this->lastupdate = date(self::DATETIME_STYLE);' . "\n"
                . '        } else {' . "\n"
                . '            $this->creationdate = date(self::DATETIME_STYLE);' . "\n"
                . '            $this->lastnick = null;' . "\n"
                . '            $this->lastupdate = null;' . "\n"
                . '            $this->nick = Session::get(\'user\') ? Session::get(\'user\')->nick : null;' . "\n"
                . '        }' . "\n\n";
        }

        $sample .= $test
            . '    }' . "\n"
            . '}' . "\n";
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
            'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View', 'Model/Join', 'Table', 'Translation', 'View', 'XMLView',
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
        $this->createInit();
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
        foreach ($fields as $field) {
            if ($field->tipo === 'character varying') {
                $field->tipo .= '(' . $field->longitud . ')';
            }

            $columns .= "    <column>\n"
                . "        <name>$field->nombre</name>\n"
                . "        <type>$field->tipo</type>\n";

            if ($field->tipo === 'serial' || $field->requerido) {
                $columns .= "        <null>NO</null>\n";
            }

            $columns .= "    </column>\n";

            if ($field->tipo === 'serial') {
                $constraints .= "    <constraint>\n"
                    . '        <name>' . $tableName . "_pkey</name>\n"
                    . '        <type>PRIMARY KEY (' . $field->nombre . ")</type>\n"
                    . "    </constraint>\n";
            }

            if ($field->nombre === 'nick' || $field->nombre === 'lastnick') {
                $constraints .= "    <constraint>\n"
                    . "        <name>ca_" . $tableName . "_users_" . $field->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $field->nombre . ") REFERENCES users (nick) ON DELETE SET NULL ON UPDATE CASCADE</type>\n"
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

        $fieldDefault = [];
        foreach ($fields as $field) {
            // guardamos las columnas por defecto aparte
            if ($this->globalFields && in_array($field->nombre, ['creationdate', 'lastnick', 'lastupdate', 'nick'])) {
                $fieldDefault[] = $field;
                continue;
            }

            // si la columna es de tipo serial, la ponemos al principio
            if ($field->tipo === 'serial') {
                $columns = $this->getWidget($field, $order, $tabForColums) . $columns;
                $order += 10;
                continue;
            }

            $columns .= $this->getWidget($field, $order, $tabForColums);
            $order += 10;
        }

        if (count($fieldDefault) > 0) {
            // ordenamos el array de columnas poniendo este orden: creationdate, nick, lastupdate, lastnick
            usort($fieldDefault, function ($a, $b) {
                $order = ['creationdate' => 1, 'nick' => 2, 'lastupdate' => 3, 'lastnick' => 4];
                return $order[$a->nombre] <=> $order[$b->nombre];
            });
        }

        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<view>' . "\n"
            . '    <columns>' . "\n";

        switch ($editOrList) {
            case 0: // Es un ListController
                $sample .= $columns;

                // añadimos las columnas por defecto al final
                if ($this->globalFields) {
                    foreach ($fieldDefault as $field) {
                        $sample .= $this->getWidget($field, $order, $tabForColums);
                        $order += 10;
                    }
                }
                break;

            case 1: // Es un EditController
            case 2: // Es una extensión
                $sample .= '        <group name="' . $groupName . '" numcolumns="12">' . "\n"
                    . $columns
                    . '        </group>' . "\n";

                // añadimos el grupo de logs
                if ($this->globalFields) {
                    $order = 100;
                    $sample .= '        <group name="logs" numcolumns="12">' . "\n";
                    foreach ($fieldDefault as $field) {
                        $sample .= $this->getWidget($field, $order, $tabForColums);
                        $order += 10;
                    }
                    $sample .= '        </group>' . "\n";
                }
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

    private function getWidget(Columna $column, string $order, int $tabForColums): string
    {
        $spaces = str_repeat(" ", $tabForColums);
        $sample = '';

        $max = is_null($column->maximo) ? '' : ' max="' . $column->maximo . '"';
        $maxlength = is_null($column->longitud) ? '' : ' maxlength="' . $column->longitud . '"';
        $min = is_null($column->minimo) ? '' : ' min="' . $column->minimo . '"';
        $nombreColumn = $column->nombre;
        $nombreWidget = $column->nombre;
        $step = is_null($column->step) ? '' : ' step="' . $column->step . '"';
        $requerido = $column->requerido ? ' required="true"' : '';

        switch ($nombreWidget) {
            case 'creationdate':
                $nombreColumn = 'creation-date';
                break;

            case 'lastnick':
                $nombreColumn = 'last-user';
                break;

            case 'lastupdate':
                $nombreColumn = 'last-update';
                break;

            case 'nick':
                $nombreColumn = 'user';
                break;
        }

        switch ($column->tipo) {
            default:
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $nombreWidget . '"' . $maxlength . $requerido . '/>' . "\n";
                break;

            case 'serial':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $nombreWidget . '" readonly="true"/>' . "\n";
                break;

            case 'double precision':
            case 'integer':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="number" fieldname="' . $nombreWidget . '"' . $max . $min . $step . $requerido . '/>' . "\n";
                break;

            case 'boolean':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="checkbox" fieldname="' . $nombreWidget . '"' . $requerido . '/>' . "\n";
                break;

            case 'text':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="textarea" fieldname="' . $nombreWidget . '"' . $requerido . '/>' . "\n";
                break;

            case 'timestamp':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="datetime" fieldname="' . $nombreWidget . '"' . $requerido . '/>' . "\n";
                break;

            case 'date':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="date" fieldname="' . $nombreWidget . '"' . $requerido . '/>' . "\n";
                break;

            case 'time':
                $sample .= $spaces . '<column name="' . $nombreColumn . '" display="' . $column->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="time" fieldname="' . $nombreWidget . '"' . $requerido . '/>' . "\n";
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
