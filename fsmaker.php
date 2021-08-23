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

    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const VERSION = 0.7;
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
                $this->createGitIgnore($name);
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

            case 'translations':
                $this->updateTranslationsAction();
                break;

            default:
                $this->help();
                break;
        }
    }

    private function askFields(): array
    {
        $fields = [];
        $serial = false;

        while (true) {
            echo "\n";
            $name = $this->prompt('Nombre del campo (vacío para terminar)');
            if (empty($name)) {
                break;
            }

            $type = $this->askType($serial);
            switch ($type) {
                case 1:
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
        $fileName = $this->isCoreFolder() ? 'Core/Controller/' . $name . '.php' : 'Controller/' . $name . '.php';
        if (file_exists($fileName)) {
            echo "* El controlador " . $name . " YA EXISTE.\n";
            return;
        } elseif (empty($name)) {
            echo '* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n';
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/Controller.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]']
            , [$this->getNamespace(), $name]
            , $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $viewFilename = $this->isCoreFolder() ? 'Core/View/' . $name . '.html.twig' : 'View/' . $name . '.html.twig';
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
        $fileName = $this->isCoreFolder() ? 'Core/Controller/Edit' . $modelName . '.php' : 'Controller/Edit' . $modelName . '.php';
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

        $xmlFilename = $this->isCoreFolder() ? 'Core/XMLView/Edit' . $modelName . '.xml' : 'XMLView/Edit' . $modelName . '.xml';
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        $this->createXMLControllerByFields($xmlFilename, $fields, 1);
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createControllerList(string $modelName, array $fields)
    {
        $menu = $this->prompt('Menú');
        $title = $this->prompt('Título');
        $fileName = $this->isCoreFolder() ? 'Core/Controller/List' . $modelName . '.php' : 'Controller/List' . $modelName . '.php';
        if (file_exists($fileName)) {
            echo "* El controlador " . $fileName . " YA EXISTE.\n";
            return;
        } elseif (empty($modelName)) {
            echo '* No introdujo el nombre del Controlador';
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/ListController.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]']
            , [$this->getNamespace(), $modelName, $title, $menu]
            , $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;

        $xmlFilename = $this->isCoreFolder() ? 'Core/XMLView/List' . $modelName . '.xml' : 'XMLView/List' . $modelName . '.xml';
        if (file_exists($xmlFilename)) {
            echo '* ' . $xmlFilename . " YA EXISTE\n";
            return;
        }

        $this->createXMLControllerByFields($xmlFilename, $fields, 0);
        echo '* ' . $xmlFilename . self::OK;
    }

    private function createCron(string $name)
    {
        if (empty($name)) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = $name . "/Cron.php";
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
            echo '* No introdujo el nombre del controlador a extender.\n';
            return;
        }

        $fileName = 'Extension/Controller/' . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del controlador " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/extensionController.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

        $this->modifyInit($name, 1);
    }

    private function createExtensionModel(string $name)
    {
        if (empty($name)) {
            echo '* No introdujo el nombre del modelo a extender.\n';
            return;
        }

        $fileName = 'Extension/Model/' . $name . '.php';
        if (file_exists($fileName)) {
            echo "* La extensión del modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/extensionModel.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . "\n";

        $this->modifyInit($name, 0);
    }

    private function createExtensionTable(string $name)
    {
        if (empty($name)) {
            echo '* No introdujo el nombre de la tabla a extender.\n';
            return;
        }

        $fileName = 'Extension/Table/' . $name . '.xml';
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
            echo '* No introdujo el nombre del XMLView a extender.\n';
            return;
        }

        $fileName = 'Extension/XMLView/' . $name . '.xml';
        if (file_exists($fileName)) {
            echo "* El fichero " . $fileName . " YA EXISTE.\n";
            return;
        }

        $sample = file_get_contents(__DIR__ . "/SAMPLES/extensionXMLView.xml.sample");
        $template = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample); // Por si el día de mañana hubiera que reemplazar algo
        file_put_contents($fileName, $template);
        echo '* ' . $fileName . self::OK;
    }

    private function createGitIgnore(string $name)
    {
        if (empty($name)) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = $name . '/.gitignore';
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
        if (empty($name)) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = $name . "/facturascripts.ini";
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
        if (empty($name)) {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        $fileName = $name . "/Init.php";
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
            echo '* No introdujo ni el modelo ni la tabla.\n';
            return;
        }

        $fileName = $this->isCoreFolder() ? 'Core/Model/' . $name . '.php' : 'Model/' . $name . '.php';
        if (file_exists($fileName)) {
            echo "* El modelo " . $name . " YA EXISTE.\n";
            return;
        }

        $fields = $this->askFields();
        $this->createModelByFields($fileName, $tableName, $fields, $name);
        echo '* ' . $fileName . self::OK;

        $tableFilename = $this->isCoreFolder() ? 'Core/Table/' . $tableName . '.xml' : 'Table/' . $tableName . '.xml';
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
        
        foreach ($fields as $property => $type) {
            // Para la creación de properties
            $properties .= "    public $" . $property . ";" . "\n\n";
            
            // Para la creación de la primaryColumn
            if ($type === 'serial') {
                $primaryColumn = $property;
            }
            
            // Para el método clear()
            switch ($type) {
                case 'serial':
                    $primaryColumn = $property;
                    break;

                case 'integer':
                    $clear .= '        $this->' . $property . ' = 0;' . "\n";
                    break;

                case 'double precision':
                    $clear .= '        $this->' . $property . ' = 0;' . "\n";
                    break;

                case 'boolean':
                    $clear .= '        $this->' . $property . ' = false;' . "\n";
                    break;

                case 'timestamp':
                    $clear .= '        $this->' . $property . ' = date("d-m-Y H:i:s");' . "\n";
                    break;

                case 'date':
                    $clear .= '        $this->' . $property . ' = date("d-m-Y");' . "\n";
                    break;

                case 'time':
                    $clear .= '        $this->' . $property . ' = date("H:i:s");' . "\n";
                    break;
            }
            
        }

        $sample = '<?php' . "\n"
            . 'namespace FacturaScripts\\' . $this->getNamespace() . '\Model;' . "\n\n"
            . 'class ' . $name . ' extends \FacturaScripts\Core\Model\Base\ModelClass' . "\n"
            . '{' . "\n"
            . '    use \FacturaScripts\Core\Model\Base\ModelTrait;' . "\n\n"
            . $properties
            . '    public function clear() {' . "\n"
            . '        parent::clear();' . "\n"
            . $clear
            . '    }' . "\n\n"
            . '    public static function primaryColumn() {' . "\n"
            . '        return "' . $primaryColumn . '";' . "\n"
            . '    }' . "\n\n"
            . '    public static function tableName() {' . "\n"
            . '        return "' . $tableName . '";' . "\n"
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
            echo '* El plugin debe tener un nombre.\n';
            return;
        } elseif (file_exists($name)) {
            echo "* El plugin " . $name . " YA EXISTE.\n";
            return;
        }

        mkdir($name, 0755);
        echo '* ' . $name . self::OK;

        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES', 'Extension/Controller',
            'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Model/Join', 'Table', 'Translation', 'View', 'XMLView'
        ];
        foreach ($folders as $folder) {
            mkdir($name . '/' . $folder, 0755, true);
            echo '* ' . $name . '/' . $folder . self::OK;
        }

        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            file_put_contents(
                $name . '/Translation/' . $filename . '.json',
                '{"' . strtolower($name) . '": "' . $name . '"}'
            );
            echo '* ' . $name . '/Translation/' . $filename . ".json" . self::OK;
        }

        $this->createGitIgnore($name);
        $this->createCron($name);
        $this->createIni($name);
        $this->createInit($name);
    }

    private function createXMLControllerByFields(string $xmlFilename, array $fields, int $editOrList)
    {
        if (empty($fields)) {
            $fields = $this->askFields();
        }

        // Creamos el xml con los campos introducidos
        $order = 100;
        $columns = '';
        foreach ($fields as $key => $type) {
            $columns .= $this->getWidget($key, $type, $order);
            $order += 10;
        }

        if ($editOrList === 1) {
            // Es un EditController
            $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<view>' . "\n"
                . '    <columns>' . "\n"
                . '        <group name="data" numcolumns="12">' . "\n"
                . $columns
                . '        </group>' . "\n"
                . '    </columns>' . "\n"
                . '</view>' . "\n";
            file_put_contents($xmlFilename, $sample);
            return;
        }

        // Es un ListController
        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<view>' . "\n"
            . '    <columns>' . "\n"
            . $columns
            . '    </columns>' . "\n"
            . '</view>' . "\n";
        file_put_contents($xmlFilename, $sample);
    }

    private function createXMLTableByFields(string $tableFilename, string $tableName, array $fields)
    {
        $columns = '';
        $constraints = '';
        foreach ($fields as $key => $type) {
            $columns .= "    <column>\n"
                . "        <name>$key</name>\n"
                . "        <type>$type</type>\n"
                . "    </column>\n";

            if ($type === 'serial') {
                $constraints .= "    <constraint>\n"
                    . '        <name>' . $tableName . "_pkey</name>\n"
                    . '        <type>PRIMARY KEY (' . $key . ")</type>\n"
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

    private function findPluginName(): string
    {
        if ($this->isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return $ini['name'] ?? '';
        }

        return '';
    }

    private function getWidget(string $name, string $type, string $order): string
    {
        $sample = '';
        switch ($type) {
            default:
                $sample .= '            <column name="' . $name . '" order="' . $order . '">' . "\n"
                    . '                <widget type="text" fieldname="' . $name . '" />' . "\n";
                break;

            case 'double precision':
                $sample .= '            <column name="' . $name . '" display="right" order="' . $order . '">' . "\n"
                    . '                <widget type="number" fieldname="' . $name . '" />' . "\n";
                break;
            
            case 'int':
                $sample .= '            <column name="' . $name . '" display="right" order="' . $order . '">' . "\n"
                    . '                <widget type="number" fieldname="' . $name . '" />' . "\n";
                break;

            case 'boolean':
                $sample .= '            <column name="' . $name . '" display="center" order="' . $order . '">' . "\n"
                    . '                <widget type="checkbox" fieldname="' . $name . '" />' . "\n";
                break;

            case 'text':
                $sample .= '            <column name="' . $name . '" order="' . $order . '">' . "\n"
                    . '                <widget type="textarea" fieldname="' . $name . '" />' . "\n";
                break;

            case 'timestamp':
                $sample .= '            <column name="' . $name . '" order="' . $order . '">' . "\n"
                    . '                <widget type="datetime" fieldname="' . $name . '" />' . "\n";
                break;

            case 'date':
                $sample .= '            <column name="' . $name . '" order="' . $order . '">' . "\n"
                    . '                <widget type="date" fieldname="' . $name . '" />' . "\n";
                break;

            case 'time':
                $sample .= '            <column name="' . $name . '" order="' . $order . '">' . "\n"
                    . '                <widget type="time" fieldname="' . $name . '" />' . "\n";
                break;
        }

        $sample .= "            </column>\n";
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
            . "$ fsmaker init\n\n"
            . "descargar:\n"
            . "$ fsmaker translations\n";
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
        if (!file_exists($fileName)) {
            $this->createInit($name);
        }

        $fileStr = file_get_contents($fileName);
        $toSearch = '/// se ejecutara cada vez que carga FacturaScripts (si este plugin está activado).';
        $toChange = $toSearch . "\n" . '        $this->loadExtension(new Extension\Controller\\' . $name . '())';
        if ($modelOrController === 0) {
            $toChange = "\n" . '        $this->loadExtension(new Extension\Model\\' . $name . '())';
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
            $ini = parse_ini_file('facturascripts.ini');
            $project = $ini['name'] ?? '';
        } elseif ($this->isCoreFolder()) {
            $folder = 'Core/Translation/';
            $project = 'CORE-2018';
        } else {
            echo "* Esta no es la carpeta raíz del plugin.\n";
            return;
        }

        if (empty($project)) {
            echo "Proyecto desconocido.\n";
            return;
        }

        /// download json from facturascripts.com
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
}

new fsmaker($argv);