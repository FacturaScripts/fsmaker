<?php

/**
 * @author Carlos García Gómez            <carlos@facturascripts.com>
 * @author Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 */
if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

final class fsmaker {

    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const VERSION = 0.4;
    const OK = " -> OK.\n";

    public function __construct($argv) {
        if (count($argv) < 2) {
            echo $this->help();
            return;
        }

        switch ($argv[1]) {
            case 'plugin':
                echo $this->createPluginAction();
                return;
            
            case 'model':
                echo $this->createModelAction();
                return;
            
            case 'controller':
                echo $this->createControllerAction();
                return;
            
            case 'translations':
                echo $this->updateTranslationsAction();
                return;
            
            case 'extension':
                echo $this->createExtensionAction();
                return;
            
            case 'gitignore':
                echo $this->createGitIgnore("");
                return;
            
            case 'cron':
                echo $this->createCron("");
                return;
            
            case 'init':
                echo $this->createInit("");
                return;
            
            default:
                echo $this->help();
                return;
        }
        
    }
    
    private function askByFields(&$array_fields, &$array_types) {
        $haySerial = false;
        
        while (true) {
            echo "\n";

            $field = (string) $this->prompt('Nombre del field(vacío = EXIT de crear fields)');
            if ($field === "") {
                return;
            }

            $type = $this->askByType($array_fields, $haySerial);

            switch ($type) {
                case 1:
                    $array_fields[] = $field;
                    $array_types[] = 'serial';
                    break;

                case 2:
                    $array_fields[] = $field;
                    $array_types[] = 'integer';
                    break;

                case 3:
                    $array_fields[] = $field;
                    $array_types[] = 'double precision';
                    break;

                case 4:
                    $array_fields[] = $field;
                    $array_types[] = 'boolean';
                    break;

                case 5:
                    $array_fields[] = $field;
                    $cantidad = (int) $this->prompt("\nCantidad caracteres");
                    $array_types[] = "character varying($cantidad)";
                    break;

                case 6:
                    $array_fields[] = $field;
                    $array_types[] = 'text';
                    break;

                case 7:
                    $array_fields[] = $field;
                    $array_types[] = 'timestamp';
                    break;

                case 8:
                    $array_fields[] = $field;
                    $array_types[] = 'date';
                    break;

                case 9:
                    $array_fields[] = $field;
                    $array_types[] = 'time';
                    break;

                default:
                    echo "\nEl nombre de field " . $field . " no lo usaremos.\n";
                    break;
            }
        }
    }

    private function askByType(&$array_fields, &$haySerial) {
        while (true) {
            echo "\n";
            $type = (int) $this->prompt( "Elija el tipo de campo\n"
                                       . "0 = ** VOLVER A PREGUNTAR EL NOMBRE **\n"
                                       . "1 = serial\n"
                                       . "2 = integer\n"
                                       . "3 = double precision\n"
                                       . "4 = boolean\n"
                                       . "5 = character varying\n"
                                       . "6 = text\n"
                                       . "7 = timestamp\n"
                                       . "8 = date\n"
                                       . "9 = time\n" );
            switch ($type) {
                case 0:
                    return $type;

                case 1:
                    // Hemos elegido serial, así que tengo que comprobar que no exista de antes
                    if ($haySerial === true) {
                        // Ya se creó un serial
                        echo "\nYa hay un campo de tipo serial.\n";
                    } else {
                        // No se ha creado todavía un serial
                        $haySerial = true;
                        return $type;
                    }
                    
                    break;

                case 2:
                    return $type;

                case 3:
                    return $type;

                case 4:
                    return $type;

                case 5:
                    return $type;

                case 6:
                    return $type;

                case 7:
                    return $type;

                case 8:
                    return $type;

                case 9:
                    return $type;

                case 10:
                    return $type;

                default:
                    echo "\nOpción incorrecta.\n";
                    break;
            }
        }
    }
    
    private function askNamePlugin(string &$name): string {
        if (file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
            return "* No se puede crear un plugin en esta carpeta.\n";
        }
        
        // Estamos creando un Plugin, por lo que preguntaremos por el nombre de él
        $name = $this->prompt('Nombre del plugin', '/^[A-Z][a-zA-Z0-9_]*$/');
        if (empty($name)) {
            return '* No introdujo el nombre del plugin.\n';
        } elseif (file_exists($name)) {
            return "* El plugin " . $name . " YA EXISTE.\n";
        }

        return "";
    }

    private function createController(): string {
        $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
        
        $fileName = $this->isCoreFolder() ? 'Core/Controller/' . $name . '.php' : 'Controller/' . $name . '.php';
        if (file_exists($fileName)) {
            return "* El controlador " . $name . " YA EXISTE.\n";
        } elseif (empty($name)) {
            return '* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n';
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/Controller.php.sample");
        $template = str_replace( ['[[NAME_SPACE]]', '[[NAME]]']
                               , [$this->getNamespace(), $name]
                               , $sample );
        file_put_contents($fileName, $template);

        echo self::OK;

        // Creamos vista twig
        $viewFilename = $this->isCoreFolder() ? 'Core/View/' . $name . '.html.twig' : 'View/' . $name . '.html.twig';
        
        if (file_exists($viewFilename)) {
            return '* ' . $viewFilename . " YA EXISTE.\n";
        }

        echo '* ' . $viewFilename;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/View.html.twig.sample");
        $template = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample);
        file_put_contents($viewFilename, $template);

        return self::OK;
    }

    private function createControllerAction(): string {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "Esta no es la carpeta raíz del plugin.\n";
        }
        
        $option = (int) $this->prompt('Elija el tipo de controlador a crear' . "\n" . '1=Controller, 2=ListController, 3=EditController');

        switch ($option) {
            case 1:
                return $this->createController();

            case 2:
                $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $array_fields = array();
                $array_types = array();
                return $this->createControllerList($modelName, $array_fields, $array_types);

            case 3:
                $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
                $array_fields = array();
                $array_types = array();
                return $this->createControllerEdit($modelName, $array_fields, $array_types);

            default:
                return "Opción no válida.\n";
        }
        
    }

    private function createControllerEdit($modelName, array $array_fields, array $array_types): string {
        $this->fillFields($array_fields, $array_types);
        
        $fileName = $this->isCoreFolder() ? 'Core/Controller/Edit' . $modelName . '.php' : 'Controller/Edit' . $modelName . '.php';
        
        if (file_exists($fileName)) {
            return "El controlador " . $fileName . " YA EXISTE.\n";
        } elseif (empty($modelName)) {
            return '';
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/EditController.php.sample");
        $template = str_replace( ['[[NAME_SPACE]]', '[[MODEL_NAME]]']
                               , [$this->getNamespace(), $modelName]
                               , $sample );
        file_put_contents($fileName, $template);

        echo self::OK;

        $xmlviewFilename = $this->isCoreFolder() ? 'Core/XMLView/Edit' . $modelName . '.xml' : 'XMLView/Edit' . $modelName . '.xml';
        
        if (file_exists($xmlviewFilename)) {
            return '* ' . $xmlviewFilename . " YA EXISTE\n";
        }

        echo '* ' . $xmlviewFilename;
        
        $this->createXMLControllerByFields($xmlviewFilename, $array_fields, $array_types, 1);

        echo self::OK;
        return "";
    }

    private function createControllerList(string $modelName, array $array_fields, array $array_types): string {
        $this->fillFields($array_fields, $array_types);

        $menu = $this->prompt('Menú');
        $title = $this->prompt('Título');
        
        $fileName = $this->isCoreFolder() ? 'Core/Controller/List' . $modelName . '.php' : 'Controller/List' . $modelName . '.php';

        if (file_exists($fileName)) {
            return "* El controlador " . $fileName . " YA EXISTE.\n";
        } elseif (empty($modelName)) {
            return '* No introdujo el nombre del Controlador';
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/ListController.php.sample");
        $template = str_replace( ['[[NAME_SPACE]]', '[[MODEL_NAME]]', '[[TITLE]]', '[[MENU]]']
                               , [$this->getNamespace(), $modelName, $title, $menu]
                               , $sample );
        file_put_contents($fileName, $template);

        echo self::OK;

        $xmlviewFilename = $this->isCoreFolder() ? 'Core/XMLView/List' . $modelName . '.xml' : 'XMLView/List' . $modelName . '.xml';
        
        if (file_exists($xmlviewFilename)) {
            return '* ' . $xmlviewFilename . " YA EXISTE\n";
        }

        echo '* ' . $xmlviewFilename;
        
        $this->createXMLControllerByFields($xmlviewFilename, $array_fields, $array_types, 0);
        
        echo self::OK;
        return "";
    }

    private function createCron(string $name = "") {
        $folder = ".";
        if ($name === "") {
            $hayFallo = $this->searchNamePlugin($name);
            if ($hayFallo <> '') {
                return $hayFallo;
            }
        } else {
            $folder = $name;
        }

        // $fileName = $name . "/Cron.php";
         $fileName = $folder . "/Cron.php";

        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return "";
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/Cron.php.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        
        echo self::OK;
        return "";
    }

    private function createExtensionAction(): string {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "* Esta no es la carpeta raíz del plugin.\n";
        }
        
        $option = (int) $this->prompt('Elija el tipo de extensión' . "\n" . '1=Tabla, 2=Modelo, 3=Controlador, 4=XMLView');
        
        switch ($option) {
            case 1:
                $name = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
                return $this->createExtensionTable($name);
                
            case 2:
                $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
                return $this->createExtensionModel($name);
                
            case 3:
                $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
                return $this->createExtensionController($name);
                
            case 4:
                $name = $this->prompt('Nombre del XMLView', '/^[A-Z][a-zA-Z0-9_]*$/');
                return $this->createExtensionXMLView($name);
                
            default:
                return "* Opción no válida.\n";
        }
    }
    
    private function createExtensionController(string $name): string {
        if (empty($name)) {
            return '* No introdujo el nombre del controlador a extender.\n';
        }

        $fileName = 'Extension/Controller/' . $name . '.php';
        if (file_exists($fileName)) {
            return "* La extensión del controlador " . $name . " YA EXISTE.\n";
        }

        echo '* ' . $fileName . "\n";
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/extensionController.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        //$template = str_replace(, $this->getNamespace(), $sample);
        
        file_put_contents($fileName, $template);
        
        return $this->modifyInit($name, 1);
    }

    private function createExtensionModel(string $name): string {
        if (empty($name)) {
            return '* No introdujo el nombre del modelo a extender.\n';
        }

        $fileName = 'Extension/Model/' . $name . '.php';
        if (file_exists($fileName)) {
            return "* La extensión del modelo " . $name . " YA EXISTE.\n";
        }

        echo '* ' . $fileName . "\n";
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/extensionModel.php.sample");
        $template = str_replace(['[[NAME]]', '[[NAME_SPACE]]'], [$name, $this->getNamespace()], $sample);
        file_put_contents($fileName, $template);
        
        return $this->modifyInit($name, 0);
    }

    private function createExtensionTable(string $name): string {
        if (empty($name)) {
            return '* No introdujo el nombre de la tabla a extender.\n';
        }

        $fileName = 'Extension/Table/' . $name . '.xml';
        if (file_exists($fileName)) {
            return "* La extensión de la tabla " . $name . " YA EXISTE.\n";
        }

        $array_fields = array();
        $array_types = array();
        
        $this->askByFields($array_fields, $array_types);
        $this->fillFields($array_fields, $array_types);
        
        echo '* ' . $fileName;
        
        $this->createXMLTableByFields($fileName, $name, $array_fields, $array_types);

        return self::OK;
    }

    private function createExtensionXMLView($name) {
        if (empty($name)) {
            return '* No introdujo el nombre del XMLView a extender.\n';
        }

        $fileName = 'Extension/XMLView/' . $name . '.xml';
        if (file_exists($fileName)) {
            return "* El fichero " . $fileName . " YA EXISTE.\n";
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/extensionXMLView.xml.sample");
        $template = str_replace('[[NADA_A_REEMPLAZAR]]', $name, $sample); // Por si el día de mañana hubiera que reemplazar algo
        file_put_contents($fileName, $template);
        
        return self::OK;
    }
    
    private function createGitIgnore(string $name) {
        $folder = ".";
        
        if ($name === "") {
            $respuesta = $this->searchNamePlugin($name);
            if ($respuesta <> '') {
                return $respuesta;
            }
        } else {
            $folder = $name;
        }

        $fileName = $folder . '/.gitignore';

        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return "";
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/gitignore.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        
        echo self::OK;
        return "";
    }

    private function createIni(string $name) {
        $fileName = $name . "/facturascripts.ini";

        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return "";
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/facturascripts.ini.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);

        echo self::OK;
        return "";
    }

    private function createInit(string $name) {
        $folder = ".";
        
        if ($name === "") {
            $respuesta = $this->searchNamePlugin($name);
            if ($respuesta <> '') {
                return $respuesta;
            }
        } else {
            $folder = $name;
        }

        $fileName = $folder . "/Init.php";

        if (file_exists($fileName)) {
            echo '* ' . $fileName . " YA EXISTE\n";
            return "";
        }

        echo '* ' . $fileName;
        
        $path_parts = pathinfo(__FILE__);
        $sample = file_get_contents($path_parts['dirname']. "/SAMPLES/Init.php.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        
        file_put_contents($fileName, $template);

        echo self::OK;
        return "";
    }

    private function createModelAction(): string {
        if (false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "* Esta no es la carpeta raíz del plugin.\n";
        }

        $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
        $tableName = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));

        echo "\n\n";

        if (empty($name) || empty($tableName)) {
            return '* No introdujo ni el modelo ni la tabla.\n';
        }

        $fileName = $this->isCoreFolder() ? 'Core/Model/' . $name . '.php' : 'Model/' . $name . '.php';
        if (file_exists($fileName)) {
            return "* El modelo " . $name . " YA EXISTE.\n";
        }

        $array_fields = array();
        $array_types = array();
            
        $this->askByFields($array_fields, $array_types);
        $this->fillFields($array_fields, $array_types);
        
        echo '* ' . $fileName;
        
        $this->createModelByFields($fileName, $tableName, $array_fields, $name);
        
        echo self::OK;

        $tableFilename = $this->isCoreFolder() ? 'Core/Table/' . $tableName . '.xml' : 'Table/' . $tableName . '.xml';
        if (false === file_exists($tableFilename)) {
            echo '* ' . $tableFilename;

            $this->createXMLTableByFields($tableFilename, $tableName, $array_fields, $array_types);

            echo self::OK;
        } else {
            echo "\n" . '* ' . $tableFilename . " YA EXISTE";
        }

        echo "\n";

        if ($this->prompt('¿Crear EditController? 1=Si, 0=No') === '1') {
            $this->createControllerEdit($name, $array_fields, $array_types);
        }
        echo "\n";

        if ($this->prompt('¿Crear ListController? 1=Si, 0=No') === '1') {
            $this->createControllerList($name, $array_fields, $array_types);
        }

        return "";
    }
    
    private function createModelByFields($fileName, $tableName, $array_fields, $name) : string {
        $sample = "";
        
        foreach ($array_fields as $key => $field) {
            $sample = $sample 
                . "    public $" . $array_fields[$key] . ";\n";
        }                

        if ($sample <> "") {
            // Se introdujeron campos
            $sample = '<?php' . "\n"
                    . 'namespace FacturaScripts\[[NAME_SPACE]]\Model;' . "\n"
                    . ' ' . "\n"
                    . 'class [[NAME]] extends \FacturaScripts\Core\Model\Base\ModelClass' . "\n"
                    . '{' . "\n"
                    . '    use \FacturaScripts\Core\Model\Base\ModelTrait;' . "\n"
                    . ' ' . "\n"
                    . $sample
                    . ' ' . "\n"
                    . '    public function clear() {' . "\n"
                    . '        parent::clear();' . "\n"
                    . '        $this->creationdate = \date(self::DATETIME_STYLE);' . "\n"
                    . '    }' . "\n"
                    . ' ' . "\n"
                    . '    public static function primaryColumn() {' . "\n"
                    . '        return "id";' . "\n"
                    . '    }' . "\n"
                    . ' ' . "\n"
                    . '    public static function tableName() {' . "\n"
                    . '        return "[[TABLE_NAME]]";' . "\n"
                    . '    }' . "\n"
                    . ' ' . "\n"
                    . '}' . "\n"
                    . ' ' . "\n";
            
            $sample = str_replace(['[[NAME]]', '[[NAME_SPACE]]', '[[TABLE_NAME]]'], [$name, $this->getNamespace(), $tableName], $sample);
            file_put_contents($fileName, $sample);
        }
        
        return $sample;
    }
    
    private function createPluginAction(): string {
        $name = "";
        
        $respuesta = $this->askNamePlugin($name);
        if ($respuesta <> '') {
            return $respuesta;
        }

        echo '* ' . $name;

        mkdir($name, 0755);

        echo self::OK;

        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES', 'Extension/Controller',
            'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Model/Join', 'Table', 'Translation', 'View', 'XMLView'
        ];

        foreach ($folders as $folder) {
            echo '* ' . $name . '/' . $folder . "";
            mkdir($name . '/' . $folder, 0755, true);
            echo self::OK;
        }

        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            echo '* ' . $name . '/Translation/' . $filename . ".json";
            file_put_contents($name . '/Translation/' . $filename . '.json', '{
    "' . $name . '": "' . $name . '"
}');
            echo self::OK;
        }

        $this->createGitIgnore($name);
        $this->createCron($name);
        $this->createIni($name);
        $this->createInit($name);

        return "";
    }

    private function createXMLControllerByFields($xmlviewFilename, $array_fields, $array_types, $editOrList) : string {
        // var_dump($array_fields, $array_types);
        
        if (empty($array_fields)) {
            $this->askByFields($array_fields, $array_types);
        }
        
        if ($editOrList === 1) {
            // Es un EditController
            $spaceA = '            ';
            $spaceB = '                ';
        } else {
            // Es un ListController
            $spaceA = '        ';
            $spaceB = '            ';
        }

        // Creamos el .xml con los campos introducidos
        $orden = 100;
        $sample = "";
        
        foreach ($array_fields as $key => $field) {
            $this->devuelveWidget($sample, $array_fields[$key], $array_types[$key], $orden, $spaceA, $spaceB, $editOrList);
            $orden = $orden + 10;
        }

        if ($sample <> "") {
            // Se introdujeron campos
        
            if ($editOrList === 1) {
                // Es un EditController
                $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                        . '<view>' . "\n"
                        . '    <columns>' . "\n"
                        . '        <group name="data" numcolumns="12">' . "\n"
                        . $sample;

                $sample = $sample 
                        . '        </group>' . "\n"
                        . '    </columns>' . "\n"
                        . '</view>' . "\n";
            } else {
                // Es un ListController
                $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                        . '<view>' . "\n"
                        . '    <columns>' . "\n"
                        . $sample;

                $sample = $sample 
                        . '    </columns>' . "\n"
                        . '</view>' . "\n";
            }
            
            file_put_contents($xmlviewFilename, $sample);
        }
        
        return $sample;
    }

    private function createXMLTableByFields($tableFilename, $tableName, &$array_fields, &$array_types) : string {
        // Creamos el .xml con los campos introducidos
        $sample = "";
        foreach ($array_fields as $key => $field) {
            $sample = $sample 
                . "    <column>\n"
                . "        <name>$array_fields[$key]</name>\n"
                . "        <type>$array_types[$key]</type>\n"
                . "    </column>\n";
        }                

        if ($sample <> "") {
            // Se introdujeron campos
            $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                    . '<table>' . "\n"
                    . $sample;


            // Crear primary key
            foreach ($array_fields as $key => $field) {
                if ($array_types[$key] === 'serial'){
                    $sample = $sample 
                        . "\n"
                        . "    <constraint>\n"
                        . '        <name>' . $tableName . "_pkey</name>\n"
                        . '        <type>PRIMARY KEY (' . $array_fields[$key] . ")</type>\n"
                        . "    </constraint>\n";
                }
            }

            $sample = $sample 
                    . '</table>' . "\n";
                            
            file_put_contents($tableFilename, $sample);
        }
        
        return $sample;
    }

    private function devuelveWidget(&$sample, $nombreCampo, $tipo, $orden, $spaceA, $spaceB, $editOrList) {
        switch ($tipo) {
            case 'serial':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="text" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'integer':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="text" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'double precision':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" display="right" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="number" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'boolean':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" display="center" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="checkbox" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'text':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="textarea" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'timestamp':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="datetime" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'date':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="date" fieldname="' . $nombreCampo . '" />' . "\n";
                break;

            case 'time':
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="time" fieldname="' . $nombreCampo . '" />' . "\n";
                break;
        }

        if (substr($tipo, 0, 4) === 'char') { // char = character varying($cantidad)
            $cantidad = $this->getInt($tipo);
            if ($editOrList === 1) {
                // Es un EditController
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" maxlength="' . $cantidad . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="text" fieldname="' . $nombreCampo . '" />' . "\n";
            } else {
                // Es un ListController
                $sample = $sample 
                        . $spaceA . '<column name="' . $nombreCampo . '" order="' . $orden . '">' . "\n"
                        . $spaceB . '<widget type="text" fieldname="' . $nombreCampo . '" />' . "\n";
            }
        }

        $sample = $sample 
                . $spaceA . '</column>' . "\n";

    }

    private function fillFields(&$array_fields, &$array_types) {
        if (empty($array_fields)) {
            $array_fields[] = 'id';
            $array_types[] = 'serial';
            
            $array_fields[] = 'name';
            $array_types[] = "character varying(150)";
            
            $array_fields[] = 'creationdate';
            $array_types[] = 'timestamp';
        }
    }
        
    private function getInt($s){
        return($a = preg_replace('/[^\-\d]*(\-?\d*).*/','$1', $s)) ? $a:'0';
    }
    
    private function getNamespace(): string {
        if ($this->isCoreFolder()) {
            return 'Core';
        }

        $ini = parse_ini_file('facturascripts.ini');
        return 'Plugins\\' . $ini['name'];
    }

    private function help(): string {
        return 'FacturaScripts Maker v' . self::VERSION . "

create:
$ fsmaker plugin
$ fsmaker model
$ fsmaker controller
$ fsmaker extension
$ fsmaker gitignore
$ fsmaker cron
$ fsmaker init

download:
$ fsmaker translations\n";
    }

    private function isCoreFolder(): bool {
        return file_exists('Core/Translation') && false === file_exists('facturascripts.ini');
    }

    private function isPluginFolder(): bool {
        return file_exists('facturascripts.ini');
    }

    private function modifyInit(string $name, int $modelOrController): string {
        $fileName = "./Init.php";

        if (!file_exists($fileName)) {
            echo $this->createInit("");
        }

        echo '* ' . $fileName;
        
        $fileStr = file_get_contents($fileName);
        $toSearch = '/// se ejecutara cada vez que carga FacturaScripts (si este plugin está activado).';

        $toChange = $toSearch. "\n";
        if ($modelOrController === 0) {
            $toChange = $toChange
                      . '        $this->loadExtension(new Extension\\Model\\' . $name . "())";
        } else {
            $toChange = $toChange
                      . '        $this->loadExtension(new Extension\\Controller\\' . $name . "())";
        }

        $newFileStr = str_replace($toSearch, $toChange, $fileStr);

        file_put_contents($fileName, $newFileStr);

        return self::OK;
    }

    private function prompt($label, $pattern = ''): string {
        echo $label . ': ';
        $matches = [];
        $value = trim(fgets(STDIN));
        if (!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            echo "Valor no válido. Debe cumplir: " . $pattern . "\n";
            return '';
        }

        return $value;
    }

    private function searchNamePlugin(string &$name): string {
        // Estamos dentro de la carpeta principal de un plugin, por lo que vamos a ver como se llama (facturascripts.ini)
        $name = '';
        if ($this->isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            $name = $ini['name'] ?? '';
        } else {
            return "* Esta no es la carpeta raíz del plugin.\n";
        }

        if (empty($name)) {
            return "* Nombre del plugin desconocido.\n";
        }
        
        return "";
    }

    private function updateTranslationsAction(): string {
        $folder = '';
        $project = '';
        
        if ($this->isPluginFolder()) {
            $folder = 'Translation/';
            $ini = parse_ini_file('facturascripts.ini');
            $project = $ini['name'] ?? '';
        } elseif ($this->isCoreFolder()) {
            $folder = 'Core/Translation/';
            $project = 'CORE-2018';
        } else {
            return "Esta no es la carpeta raíz del plugin.\n";
        }

        if (empty($project)) {
            return "Proyecto desconocido.\n";
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

        return "";
    }

}

new fsmaker($argv);

