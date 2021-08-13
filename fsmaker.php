<?php
/**
 * @author  Carlos García Gómez <carlos@facturascripts.com>
 */

if (php_sapi_name() !== 'cli') {
    die("Usar: php fsmaker.php");
}

class fsmaker
{
    const TRANSLATIONS = 'ca_ES,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pt_PT,va_ES';
    const VERSION = 0.4;
    const OK = " -> OK.\n";
    

    public function __construct($argv)
    {
        if(count($argv) < 2) {
            echo $this->help();
        } elseif($argv[1] === 'plugin') {
            echo $this->createPluginAction();
        } elseif($argv[1] === 'model') {
            echo $this->createModelAction();
        } elseif($argv[1] === 'controller') {
            echo $this->createControllerAction();
        } elseif($argv[1] === 'translations') {
            echo $this->updateTranslationsAction();
        } elseif($argv[1] === 'extension') {
            echo $this->createExtensionAction();
        } elseif($argv[1] === 'gitignore') {
            echo $this->createGitIgnore("");
        } elseif($argv[1] === 'cron') {
            echo $this->createCron("");
        } elseif($argv[1] === 'init') {
            echo $this->createInit("");
        } else {
            echo $this->help();
        }
    }

    private function createControllerAction(): string
    {
        $option = (int) $this->prompt('1=Controller, 2=ListController, 3=EditController');
        if(false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "Esta no es la carpeta raíz del plugin.\n";
        } elseif($option === 2) {
            $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
            return $this->createListController($modelName);
        } elseif($option === 3) {
            $modelName = $this->prompt('Nombre del modelo a utilizar', '/^[A-Z][a-zA-Z0-9_]*$/');
            return $this->createEditController($modelName);
        } elseif($option < 1 || $option > 3) {
            return "Opción no válida.\n";
        }

        $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
        $filename = $this->isCoreFolder() ? 'Core/Controller/'.$name.'.php' : 'Controller/'.$name.'.php';
        if(file_exists($filename)) {
            return "* El controlador ".$name." YA EXISTE.\n";
        } elseif(empty($name)) {
            return '* No has introducido el nombre del controlador, por lo que no seguimos con su creación.\n';
        }

        echo '* '.$filename;
        
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Controller;

class '.$name.' extends \\FacturaScripts\\Core\\Base\\Controller
{
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData["title"] = "'.$name.'";
        $pageData["menu"] = "admin";
        $pageData["icon"] = "fas fa-page";
        return $pageData;
    }
    
    public function privateCore(&$response, $user, $permissions) {
        parent::privateCore($response, $user, $permissions);
        /// tu código aquí
    }
}');
        echo self::OK;
        
        // Creamos vista twig
        $viewFilename = $this->isCoreFolder() ? 'Core/View/'.$name.'.html.twig' : 'View/'.$name.'.html.twig';
        if(file_exists($viewFilename)) {
            return '* '.$viewFilename." YA EXISTE.\n";
        }

        echo '* '.$viewFilename;
        file_put_contents($viewFilename, '{% extends "Master/MenuTemplate.html.twig" %}

{% block body %}
    {{ parent() }}
{% endblock %}

{% block css %}
    {{ parent() }}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
{% endblock %}');
        
        return self::OK;
    }

    private function createCron(string $name="")
    {
        if ($name === ""){
            $respuesta = $this->preguntarNombrePlugin($name, false);
            if ($respuesta <> '') {
                return $respuesta;
            }
        }

        $fileName = $name."/Cron.php";
        
        if(file_exists($fileName)) {
            echo '* '.$fileName." YA EXISTE\n";
            return "";
        }
        
        echo '* '.$fileName;
        file_put_contents($fileName, "<?php
namespace FacturaScripts\\Plugins\\".$name.';

class Cron extends \\FacturaScripts\\Core\\Base\\CronClass
{
    public function run() {
        /*
        if ($this->isTimeForJob("my-job-name", "6 hours")) {
            /// su código aquí
            $this->jobDone("my-job-name");
        }
        */
    }
}');
        
        echo self::OK;
        return "";
    }

    private function createEditController($modelName): string
    {
        $filename = $this->isCoreFolder() ? 'Core/Controller/Edit'.$modelName.'.php' : 'Controller/Edit'.$modelName.'.php';
        if(file_exists($filename)) {
            return "El controlador ".$filename." YA EXISTE.\n";
        } elseif(empty($modelName)) {
            return '';
        }

        echo '* '.$filename;
        
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Controller;

class Edit'.$modelName.' extends \\FacturaScripts\\Core\\Lib\\ExtendedController\\EditController
{
    public function getModelClassName() {
        return "'.$modelName.'";
    }

    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData["title"] = "'.$modelName.'";
        $pageData["icon"] = "fas fa-search";
        return $pageData;
    }
}');
        
        
        echo self::OK;
        
        $xmlviewFilename = $this->isCoreFolder() ? 'Core/XMLView/Edit'.$modelName.'.xml' : 'XMLView/Edit'.$modelName.'.xml';
        if(file_exists($xmlviewFilename)) {
            return '* '.$xmlviewFilename." YA EXISTE\n";
        }

        echo '* '.$xmlviewFilename;
        
        file_put_contents($xmlviewFilename, '<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="data" numcolumns="12">
            <column name="code" display="none" order="100">
                <widget type="text" fieldname="id" />
            </column>
            <column name="name" order="110">
                <widget type="text" fieldname="name" />
            </column>
            <column name="creation-date" order="120">
                <widget type="datetime" fieldname="creationdate" readonly="dinamic" />
            </column>
        </group>
    </columns>
</view>');
        
        echo self::OK;
        return "";
    }

    private function createGitIgnore(string $name)
    {
        
        if ($name === ""){
            $respuesta = $this->preguntarNombrePlugin($name, false);
            if ($respuesta <> '') {
                return $respuesta;
            }
        }

        $fileName = $name.'/.gitignore';
        
        if(file_exists($fileName)) {
            echo '* '.$fileName." YA EXISTE\n";
            return "";
        }
        
        echo '* '.$fileName;
        file_put_contents($fileName, "/.idea/\n/nbproject/\n/node_modules/\n"
            ."/vendor/\n.DS_Store\n.htaccess\n*.cache\n*.lock\n.vscode\n*.code-workspace");
        
        echo self::OK;
        return "";
    }

    private function createIni(string $name)
    {
        if ($name === ""){
            $respuesta = $this->preguntarNombrePlugin($name, false);
            if ($respuesta <> '') {
                return $respuesta;
            }
        }

        $fileName = $name."/facturascripts.ini";
        
        if(file_exists($fileName)) {
            echo '* '.$fileName." YA EXISTE\n";
            return "";
        }
        
        echo '* '.$fileName;
        file_put_contents($fileName, "description = '".$name."'
min_version = 2021
name = ".$name."
version = 0.1");
        
        echo self::OK;
        return "";
    }

    private function createInit($name)
    {
        if ($name === ""){
            $respuesta = $this->preguntarNombrePlugin($name, false);
            if ($respuesta <> '') {
                return $respuesta;
            }
        }

        $fileName = $name."/Init.php";
        
        if(file_exists($fileName)) {
            echo '* '.$fileName." YA EXISTE\n";
            return "";
        }
        
        echo '* '.$fileName;
        file_put_contents($fileName, "<?php
namespace FacturaScripts\\Plugins\\".$name.";

class Init extends \\FacturaScripts\\Core\\Base\\InitClass
{
    public function init() {
        /// se ejecutar cada vez que carga FacturaScripts (si este plugin está activado).
    }

    public function update() {
        /// se ejecutar cada vez que se instala o actualiza el plugin
    }
}");
        
        echo self::OK;
        return "";
    }

    private function createListController(string $modelName): string
    {
        $menu = $this->prompt('Menú');
        $title = $this->prompt('Título');
        $filename = $this->isCoreFolder() ? 'Core/Controller/List'.$modelName.'.php' : 'Controller/List'.$modelName.'.php';
        
        if(file_exists($filename)) {
            return "* El controlador ".$filename." YA EXISTE.\n";
        } elseif(empty($modelName)) {
            return '* No introdujo el nombre del Controlador';
        }

        echo '* '.$filename;
        
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Controller;

class List'.$modelName.' extends \\FacturaScripts\\Core\\Lib\\ExtendedController\\ListController
{
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData["title"] = "'.$title.'";
        $pageData["menu"] = "'.$menu.'";
        $pageData["icon"] = "fas fa-search";
        return $pageData;
    }

    protected function createViews() {
        $this->createViews'.$modelName.'();
    }

    protected function createViews'.$modelName.'(string $viewName = "List'.$modelName.'") {
        $this->addView($viewName, "'.$modelName.'", "'.$title.'");
        $this->addOrderBy($viewName, ["id"], "id");
        $this->addOrderBy($viewName, ["name"], "name", 1);
        $this->addSearchFields($viewName, ["id", "name"]);
    }
}');
        
        echo self::OK;
        
        $xmlviewFilename = $this->isCoreFolder() ? 'Core/XMLView/List'.$modelName.'.xml' : 'XMLView/List'.$modelName.'.xml';
        if(file_exists($xmlviewFilename)) {
            return '* '.$xmlviewFilename." YA EXISTE\n";
        }

        echo '* '.$xmlviewFilename;
        
        file_put_contents($xmlviewFilename, '<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <column name="code" order="100">
            <widget type="text" fieldname="id" />
        </column>
        <column name="name" order="110">
            <widget type="text" fieldname="name" />
        </column>
        <column name="creation-date" display="right" order="120">
            <widget type="datetime" fieldname="creationdate" />
        </column>
    </columns>
</view>');
        
        echo self::OK;
        return "";
    }

    private function createModelAction(): string
    {
        $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
        $tableName = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
        
        echo "\n\n";
        
        if(empty($name) || empty($tableName)) {
            return '* No introdujo ni el modelo ni la tabla.\n';
        } elseif(false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "* Esta no es la carpeta raíz del plugin.\n";
        }

        $filename = $this->isCoreFolder() ? 'Core/Model/'.$name.'.php' : 'Model/'.$name.'.php';
        if(file_exists($filename)) {
            return "* El modelo ".$name." YA EXISTE.\n";
        }

        echo '* '.$filename;
        
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Model;

class '.$name.' extends \\FacturaScripts\\Core\\Model\\Base\\ModelClass
{
    use \\FacturaScripts\\Core\\Model\\Base\\ModelTrait;

    public $creationdate;
    public $id;
    public $name;

    public function clear() {
        parent::clear();
        $this->creationdate = \date(self::DATETIME_STYLE);
    }

    public static function primaryColumn() {
        return "id";
    }

    public static function tableName() {
        return "'.$tableName.'";
    }
}');
        
        echo self::OK;
        
        
        $tableFilename = $this->isCoreFolder() ? 'Core/Table/'.$tableName.'.xml' : 'Table/'.$tableName.'.xml';
        if(false === file_exists($tableFilename)) {
            echo '* '.$tableFilename;
            file_put_contents($tableFilename, '<?xml version="1.0" encoding="UTF-8"?>
<table>
    <column>
        <name>creationdate</name>
        <type>timestamp</type>
    </column>
    <column>
        <name>id</name>
        <type>serial</type>
    </column>
    <column>
        <name>name</name>
        <type>character varying(100)</type>
    </column>
    <constraint>
        <name>'.$tableName.'_pkey</name>
        <type>PRIMARY KEY (id)</type>
    </constraint>
</table>');
            echo self::OK;
        } else {
            echo "\n".'* '.$tableFilename." YA EXISTE";
        }

        echo "\n";
        
        if($this->prompt('¿Crear EditController? 1=Si, 0=No') === '1') {
            $this->createEditController($name);
        }
        echo "\n";

        if($this->prompt('¿Crear ListController? 1=Si, 0=No') === '1') {
            $this->createListController($name);
        }
        
        return "";
    }

    private function preguntarNombrePlugin(string &$name, bool $CreandoPlugin): string
    {
        if ($CreandoPlugin === true){
            // Estamos creando un Plugin, por lo que preguntaremos por el nombre de él
            $name = $this->prompt('Nombre del plugin', '/^[A-Z][a-zA-Z0-9_]*$/');
            if(empty($name)) {
                return '* No introdujo el nombre del plugin.\n';
            } elseif(file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini')) {
                return "* No se puede crear un plugin en esta carpeta.\n";
            } elseif(file_exists($name)) {
                return "* El plugin ".$name." YA EXISTE.\n";
            }
            
            return "";
        } else {
            // Estamos dentro de la carpeta principal de un plugin, por lo que vamos a ver como se llama (facturascripts.ini)
            $name = '';
            if($this->isPluginFolder()) {
                $ini = parse_ini_file('facturascripts.ini');
                $name = $ini['name'] ?? '';
            } else {
                return "* Esta no es la carpeta raíz del plugin.\n";
            }

            if(empty($name)) {
                return "Proyecto desconocido.\n";
            }
            
            $name = "../".$name; 
            return "";
        }
        
        return "";
    }

    private function createPluginAction(): string
    {
        $name = "";
        $respuesta = $this->preguntarNombrePlugin($name, true);
        if ($respuesta <> '') {
            return $respuesta;
        }
        
        echo '* '.$name;
        
        mkdir($name, 0755);
        
        echo self::OK;
        
        
        $folders = [
            'Assets/CSS','Assets/Images','Assets/JS','Controller','Data/Codpais/ESP','Data/Lang/ES','Extension/Controller',
            'Extension/Model','Extension/Table','Extension/XMLView','Model/Join','Table','Translation','View','XMLView'
        ];
        
        foreach($folders as $folder) {
            echo '* '.$name.'/'.$folder."";
            mkdir($name.'/'.$folder, 0755, true);
            echo self::OK;
        }

        foreach(explode(',', self::TRANSLATIONS) as $filename) {
            echo '* '.$name.'/Translation/'.$filename.".json";
            file_put_contents($name.'/Translation/'.$filename.'.json', '{
    "'.$name.'": "'.$name.'"
}');
            echo self::OK;
        }

        $this->createGitIgnore($name);
        $this->createCron($name);
        $this->createIni($name);
        $this->createInit($name);
        
        return "";
    }

    private function getNamespace(): string
    {
        if($this->isCoreFolder()) {
            return 'Core';
        }

        $ini = parse_ini_file('facturascripts.ini');
        return 'Plugins\\'.$ini['name'];
    }

    private function help(): string
    {
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

    private function isCoreFolder(): bool
    {
        return file_exists('Core/Translation') && false === file_exists('facturascripts.ini');
    }

    private function isPluginFolder(): bool
    {
        return file_exists('facturascripts.ini');
    }

    private function prompt($label, $pattern = ''): string
    {
        echo $label . ': ';
        $matches = [];
        $value = trim(fgets(STDIN));
        if(!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            echo "Valor no válido. Debe cumplir: ".$pattern."\n";
            return '';
        }

        return $value;
    }

    private function updateTranslationsAction(): string
    {
        $folder = '';
        $project = '';
        if($this->isPluginFolder()) {
            $folder = 'Translation/';
            $ini = parse_ini_file('facturascripts.ini');
            $project = $ini['name'] ?? '';
        } elseif($this->isCoreFolder()) {
            $folder = 'Core/Translation/';
            $project = 'CORE-2018';
        } else {
            return "Esta no es la carpeta raíz del plugin.\n";
        }

        if(empty($project)) {
            return "Proyecto desconocido.\n";
        }

        /// download json from facturascripts.com
        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            echo "D ".$folder.$filename.".json";
            $url = "https://facturascripts.com/EditLanguage?action=json&project=".$project."&code=".$filename;
            $json = file_get_contents($url);
            if(!empty($json) && strlen($json) > 10) {
                file_put_contents($folder.$filename.'.json', $json);
                echo "\n";
                continue;
            }

            echo " - vacío\n";
        }
        
        return "";
    }

    private function createExtensionAction(): string
    {
       $option = (int) $this->prompt('Extensión de ... 1=Tabla, 2=Modelo, 3=Controlador, 4=XMLView');
        if(false === $this->isCoreFolder() && false === $this->isPluginFolder()) {
            return "Esta no es la carpeta raíz del plugin.\n";
        } elseif($option === 1) {
            $name = strtolower($this->prompt('Nombre de la tabla (plural)', '/^[a-zA-Z][a-zA-Z0-9_]*$/'));
            return $this->createExtensionTabla($name);
        } elseif($option === 2) {
            $name = $this->prompt('Nombre del modelo (singular)', '/^[A-Z][a-zA-Z0-9_]*$/');
            return $this->createExtensionModelo($name);
        } elseif($option === 3) {
            $name = $this->prompt('Nombre del controlador', '/^[A-Z][a-zA-Z0-9_]*$/');
            return $this->createExtensionControlador($name);
        } elseif($option === 4) {
            $name = $this->prompt('Nombre del XMLView', '/^[A-Z][a-zA-Z0-9_]*$/');
            return $this->createExtensionXMLView($name);
        } elseif($option < 1 || $option > 4) {
            return "Opción no válida.\n";
        }
    }
    
    private function createExtensionModelo(string $name): string
    {
        if(empty($name)) {
            return '* No introdujo el nombre del modelo a extender.\n';
        }

        $filename = 'Extension/Model/' . $name . '.php';
        if(file_exists($filename)) {
            return "* La extensión del modelo " . $name . " YA EXISTE.\n";
        }
        
        echo '* '.$filename."\n";
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Extension\\Model;

class '.$name.'
{
    // Ejemplo para añadir un método ... añadir el método usado()
    public function usado() {
        return function() {
            return $this->usado;
        };
    }
    
    // ***************************************
    // ** Métodos disponibles para extender **
    // ***************************************
    
    // clear()
    public function clear() {
       return function() {
            /// tu código aquí
         };
    }
    
    // delete() se ejecuta una vez realizado el delete() del modelo.
    public function delete() {
       return function() {
            /// tu código aquí
         };
    }
    
    // deleteBefore() se ejecuta antes de hacer el delete() del modelo. Si devolvemos false, impedimos el delete().
    public function deleteBefore() {
       return function() {
            /// tu código aquí
         };
    }

    // save() se ejecuta una vez realizado el save() del modelo.
    public function save() {
       return function() {
            /// tu código aquí
         };
    }
    
    // saveBefore() se ejecuta antes de hacer el save() del modelo. Si devolvemos false, impedimos el save().
    public function saveBefore() {
       return function() {
            /// tu código aquí
         };
    }

    // saveInsert() se ejecuta una vez realizado el saveInsert() del modelo.
    public function saveInsert() {
       return function() {
            /// tu código aquí
         };
    }
    
    // saveInsertBefore() se ejecuta antes de hacer el saveInsert() del modelo. Si devolvemos false, impedimos el saveInsert().
    public function saveInsertBefore() {
       return function() {
            /// tu código aquí
         };
    }
    
    // saveUpdate() se ejecuta una vez realizado el saveUpdate() del modelo.
    public function saveUpdate() {
       return function() {
            /// tu código aquí
         };
    }
    
    // saveUpdateBefore() se ejecuta antes de hacer el saveUpdate() del modelo. Si devolvemos false, impedimos el saveUpdate().
    public function saveUpdateBefore() {
       return function() {
            /// tu código aquí
         };
    }

}');
        $aDevolver = "La extensión del modelo fué creada correctamente ... " . $name . "\n\n"
                   . "Las extensiones de archivos xml se integran automáticamente al activar el plugin o reconstruir Dinamic.\n"
                   . "En cambio, las extensiones de archivo php se deben cargar explícitamente, y se deben hacer desde el \n"
                   . "archivo Init.php del plugin, en el método init().\n\n"
                   . "Para más información visite https://facturascripts.com/publicaciones/extensiones-de-modelos";
        return $aDevolver;
    }

    private function createExtensionTabla(string $name): string
    {
        if(empty($name)) {
            return '* No introdujo el nombre de la tabla a extender.\n';
        }

        $filename = 'Extension/Table/' . $name . '.xml';
        if(file_exists($filename)) {
            return "* La extensión de la tabla " . $name . " YA EXISTE.\n";
        }
        
        echo '* '.$filename."\n";
        file_put_contents($filename, '<?xml version="1.0" encoding="UTF-8"?>
<table>
    <column>
        <name>usado</name>
        <type>boolean</type>
    </column>
</table>

');
     
        return self::OK;
    }

    private function createExtensionControlador(string $name): string
    {
        if(empty($name)) {
            return '* No introdujo el nombre del controlador a extender.\n';
        }

        $filename = 'Extension/Controller/' . $name . '.php';
        if(file_exists($filename)) {
            return "* La extensión del controlador " . $name . " YA EXISTE.\n";
        }
        
        echo '* '.$filename."\n";
        file_put_contents($filename, '<?php
namespace FacturaScripts\\'.$this->getNamespace().'\\Extension\\Controller;

class '.$name.'
{
    // createViews() se ejecuta una vez realiado el createViews() del controlador.
    public function createViews() {
       return function() {
          /// tu código aquí
       };
    }

    // execAfterAction() se ejecuta tras el execAfterAction() del controlador.
    public function execAfterAction() {
       return function($action) {
          /// tu código aquí
       };
    }

    // execPreviousAction() se ejecuta después del execPreviousAction() del controlador. Si devolvemos false detenemos la ejecución del controlador.
    public function execPreviousAction() {
       return function($action) {
          /// tu código aquí
       };
    }

    // loadData() se ejecuta tras el loadData() del controlador. Recibe los parámetros $viewName y $view.
    public function loadData() {
       return function($viewName, $view) {
          /// tu código aquí
       };
    }

}');
     
        $aDevolver = "La extensión del controlador fué creada correctamente ... " . $name . "\n\n"
                   . "Las extensiones de archivos xml se integran automáticamente al activar el plugin o reconstruir Dinamic.\n"
                   . "En cambio, las extensiones de archivo php se deben cargar explícitamente, y se deben hacer desde el \n"
                   . "archivo Init.php del plugin, en el método init().\n\n"
                   . "Para más información visite https://facturascripts.com/publicaciones/extensiones-de-controladores";
        return $aDevolver;
        
    }

    private function createExtensionXMLView($name)
    {
        if(empty($name)) {
            return '* No introdujo el nombre del XMLView a extender.\n';
        }

        $filename = 'Extension/XMLView/' . $name . '.xml';
        if(file_exists($filename)) {
            return "* El fichero XMLView " . $name . " YA EXISTE.\n";
        }
        
        echo '* '.$filename."\n";
        file_put_contents($filename, '<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="options" numcolumns="12" valign="bottom">
           <column name="usado">
              <widget type="checkbox" fieldname="usado" />
           </column>
        </group>
    </columns>
</view>

');
    
        return self::OK;
    }

    
}
    
new fsmaker($argv);





