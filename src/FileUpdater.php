<?php
/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */

namespace fsmaker;

final class FileUpdater
{
    const OK = " -> OK.\n";

    public static function upgradeBootstrap5(): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        // Expresiones regulares y sus reemplazos
        $patterns = [
            '/([mp])l(-[0-5])/' => '$1s$2',
            '/([mp])r(-[0-5])/' => '$1e$2',
            '/no-gutters/' => 'g-0',
            '/"close"/' => '"btn-close"',
            '/btn-block/' => 'w-100',
            '/left(-[0-9]*)/' => 'start$1',
            '/right(-[0-9]*)/' => 'end$1',
            '/(float|border|rounded|text)-left/' => '$1-start',
            '/(float|border|rounded|text)-right/' => '$1-end',
            '/font-weight(-[a-zA-Z]*)/' => 'fw$1',
            '/font-style(-[a-zA-Z]*)/' => 'fst$1',
            '/form-row/' => 'row',
            '/data-toggle/' => 'data-bs-toggle',
            '/data-target/' => 'data-bs-target',
            '/data-dismiss/' => 'data-bs-dismiss',
            '/badge(-[a-zA-Z]*)/' => 'bg$1',
            '/form-group/' => 'mb-3',
            '/<span class="input-group-append">\s*(.*?)\s*<\/span>/ms' => '$1',
            '/<div class="input-group-append">\s*(.*?)\s*<\/div>/ms' => '$1',
            '/<span class="input-group-prepend">\s*(.*?)\s*<\/span>/ms' => '$1',
            '/<div class="input-group-prepend">\s*(.*?)\s*<\/div>/ms' => '$1',
            '/\)\.modal\(\)/' => ').modal(\\\'show\\\')',
            '/(<select\b[^>]*\b)(form-control\s)([^>]*>)/' => '$1form-select $3',
            '/(<select\b[^>]*\b)(\bform-control\b)([^>]*>)/' => '$1form-select$3',
            '/\s*<span\s+aria-hidden="true">\&times;<\/span>\s*/' => '',
            '/class="alert/' => 'class="alert alert-dismissible',
            '/(text-.*-)(left)/' => '$1start',
            '/(text-.*-)(right)/' => '$1end',
        ];

        $phpFiles = self::getFilesByExtension('.', 'php');
        $twigFiles = self::getFilesByExtension('.', 'twig');
        $jsFiles = self::getFilesByExtension('.', 'js');

        // filtramos archivos que no queremos procesar
        $phpFiles = self::filterTableFiles($phpFiles);
        $twigFiles = self::filterTableFiles($twigFiles);
        $jsFiles = self::filterTableFiles($jsFiles);

        // actualizamos los archivos php
        foreach ($phpFiles as $phpFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($phpFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($phpFile, $updatedContent)) {
                Utils::echo('* ' . $phpFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $phpFile . "\n");
        }

        // actualizamos los archivos twig
        foreach ($twigFiles as $twigFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($twigFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($twigFile, $updatedContent)) {
                Utils::echo('* ' . $twigFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $twigFile . "\n");
        }

        // actualizamos los archivos js
        foreach ($jsFiles as $jsFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($jsFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($jsFile, $updatedContent)) {
                Utils::echo('* ' . $jsFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $jsFile . "\n");
        }
    }

    public static function upgradePhpFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'php');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            Utils::echo("* No se han encontrado archivos php.\n");
            return;
        }

        // recorremos la lista de archivos
        foreach ($pathFiles as $pathFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($pathFile);

            // buscamos si existe las siguientes coincidencias
            // si no existen, pasamos al siguiente archivo
            if (
                strpos($fileStr, 'ToolBox::') === false &&
                strpos($fileStr, 'toolBox()') === false &&
                strpos($fileStr, 'toolbox()') === false &&
                strpos($fileStr, 'AppSettings()') === false &&
                strpos($fileStr, 'fas ') === false &&
                strpos($fileStr, 'far ') === false &&
                strpos($fileStr, 'fal ') === false &&
                strpos($fileStr, 'fat ') === false &&
                strpos($fileStr, 'fad ') === false &&
                strpos($fileStr, 'HttpFoundation') === false &&
                strpos($fileStr, 'FacturaScripts\Core\Base') === false &&
                strpos($fileStr, 'FacturaScripts\Core\Model\Base') === false &&
                strpos($fileStr, 'function clear()') === false
            ) {
                continue;
            }

            // reemplazamos log
            $searchLog = [
                'ToolBox::log(', 'ToolBox::i18nLog(',
                '$this->toolBox()->log(', '$this->toolbox()->log(', '$this->toolBox()::log(', '$this->toolbox()::log(',
                'self::toolBox()->log(', 'self::toolbox()->log(', 'self::toolBox()::log(', 'self::toolbox()::log(',
                'static::toolBox()->log(', 'static::toolbox()->log(', 'static::toolBox()::log(', 'static::toolbox()::log(',
                '$this->toolBox()->i18nLog(', '$this->toolbox()->i18nLog(', '$this->toolBox()::i18nLog(', '$this->toolbox()::i18nLog(',
                'self::toolBox()->i18nLog(', 'self::toolbox()->i18nLog(', 'self::toolBox()::i18nLog(', 'self::toolbox()::i18nLog(',
                'static::toolBox()->i18nLog(', 'static::toolbox()->i18nLog(', 'static::toolBox()::i18nLog(', 'static::toolbox()::i18nLog(',
            ];
            $fileStr = str_replace($searchLog, 'Tools::log(', $fileStr);

            // reemplazamos lang
            $searchLang = [
                'ToolBox::i18n(',
                '$this->toolBox()->i18n(', '$this->toolbox()->i18n(',
                'self::toolBox()::i18n(', 'self::toolbox()::i18n(',
                'self::toolBox()->i18n(', 'self::toolbox()->i18n(',
                'static::toolBox()::i18n(', 'static::toolbox()::i18n(',
                'static::toolBox()->i18n(', 'static::toolbox()->i18n(',
            ];
            $fileStr = str_replace($searchLang, 'Tools::lang(', $fileStr);

            // reemplazamos noHtml
            $searchNoHtml = ['ToolBox::utils()->noHtml(', 'self::toolBox()->utils()->noHtml(', '$this->toolBox()->utils()->noHtml('];
            $fileStr = str_replace($searchNoHtml, 'Tools::noHtml(', $fileStr);

            // reemplazamos settings
            $searchSettings = [
                'ToolBox::appSettings()::get(', 'ToolBox::appSettings()->get(', '$this->toolBox()->appSettings()->get(',
                'self::toolBox()->appSettings()->get(', 'self::toolBox()::appSettings()->get(', 'self::toolBox()::appSettings()::get(',
                'AppSettings()::get(', 'AppSettings()->get('
            ];
            $fileStr = str_replace($searchSettings, 'Tools::settings(', $fileStr);

            // reemplazamos date
            $searchDate = ['date(ModelCore::DATE_STYLE)', 'date(self::DATE_STYLE)'];
            $fileStr = str_replace($searchDate, 'Tools::date()', $fileStr);

            // reemplazamos dateTime
            $searchDateTime = ['date(ModelCore::DATETIME_STYLE)', 'date(self::DATETIME_STYLE)'];
            $fileStr = str_replace($searchDateTime, 'Tools::dateTime()', $fileStr);

            // reemplazamos hour
            $searchHour = ['date(ModelCore::HOUR_STYLE)', 'date(self::HOUR_STYLE)'];
            $fileStr = str_replace($searchHour, 'Tools::hour()', $fileStr);

            // reemplazamos iconos
            $oldIcons = ['fas ', 'far ', 'fal ', 'fat ', 'fad '];
            $newIcons = ['fa-solid ', 'fa-regular ', 'fa-light ', 'fa-thin ', 'fa-duotone '];
            $fileStr = str_replace($oldIcons, $newIcons, $fileStr);

            // reemplazamos Core/Base
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\AccountingFooterHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\AccountingFooterHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\AccountingHeaderHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\AccountingHeaderHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\AccountingLineHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\AccountingLineHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\AccountingModalHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\AccountingModalHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\CommonLineHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\CommonLineHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\CommonSalesPurchases;', 'use FacturaScripts\Core\Lib\AjaxForms\CommonSalesPurchases;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\PurchasesFooterHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\PurchasesFooterHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\PurchasesHeaderHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\PurchasesHeaderHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\PurchasesLineHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\PurchasesLineHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\PurchasesModalHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\PurchasesModalHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\SalesFooterHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\SalesFooterHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\SalesHeaderHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\SalesHeaderHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\SalesLineHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\SalesLineHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\AjaxForms\SalesModalHTML;', 'use FacturaScripts\Core\Lib\AjaxForms\SalesModalHTML;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\Calculator;', 'use FacturaScripts\Core\Lib\Calculator;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\CronClass;', 'use FacturaScripts\Core\Template\CronClass;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\InitClass;', 'use FacturaScripts\Core\Template\InitClass;', $fileStr);

            // reemplazamos modelos
            $fileStr = str_replace('use FacturaScripts\Core\Model\Base\ModelClass;', 'use FacturaScripts\Core\Template\ModelClass;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Model\Base\ModelTrait;', 'use FacturaScripts\Core\Template\ModelTrait;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Model\Base\ModelOnChange;', 'use FacturaScripts\Core\Template\ModelClass;', $fileStr);

            // manejamos el caso especial de "use FacturaScripts\Core\Model\Base;"
            if (strpos($fileStr, 'use FacturaScripts\Core\Model\Base;') !== false) {
                // reemplazamos el use general por los uses específicos
                $fileStr = str_replace('use FacturaScripts\Core\Model\Base;', "use FacturaScripts\Core\Template\ModelClass;\nuse FacturaScripts\Core\Template\ModelTrait;", $fileStr);

                // reemplazamos las referencias Base\ModelClass, Base\ModelTrait y Base\ModelOnChange
                $fileStr = str_replace('Base\ModelClass', 'ModelClass', $fileStr);
                $fileStr = str_replace('Base\ModelTrait', 'ModelTrait', $fileStr);
                $fileStr = str_replace('Base\ModelOnChange', 'ModelClass', $fileStr);
            }

            // reemplazamos contratos
            $fileStr = str_replace('use FacturaScripts\Core\Base\Contract\CalculatorModInterface;', 'use FacturaScripts\Core\Contract\CalculatorModInterface;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\Contract\PurchasesLineModInterface;', 'use FacturaScripts\Core\Contract\PurchasesLineModInterface;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\Contract\PurchasesModInterface;', 'use FacturaScripts\Core\Contract\PurchasesModInterface;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\Contract\SalesLineModInterface;', 'use FacturaScripts\Core\Contract\SalesLineModInterface;', $fileStr);
            $fileStr = str_replace('use FacturaScripts\Core\Base\Contract\SalesModInterface;', 'use FacturaScripts\Core\Contract\SalesModInterface;', $fileStr);

            // reemplazamos HttpFoundation
            $fileStr = str_replace('use Symfony\Component\HttpFoundation\Cookie;', '', $fileStr);
            $fileStr = str_replace('use Symfony\Component\HttpFoundation\File\UploadedFile;', 'use FacturaScripts\Core\UploadedFile;', $fileStr);
            $fileStr = str_replace('use Symfony\Component\HttpFoundation\Request;', 'use FacturaScripts\Core\Request;', $fileStr);
            $fileStr = str_replace('use Symfony\Component\HttpFoundation\Response;', 'use FacturaScripts\Core\Response;', $fileStr);

            if (strpos($fileStr, 'HttpFoundation\RedirectResponse') !== false && strpos($fileStr, 'FacturaScripts\Core\Response') !== false) {
                $fileStr = str_replace('use Symfony\Component\HttpFoundation\RedirectResponse;', '', $fileStr);
            } else {
                $fileStr = str_replace('use Symfony\Component\HttpFoundation\RedirectResponse;', 'use FacturaScripts\Core\Response;', $fileStr);
            }

            // reemplazamos Clear
            $namePlugin = Utils::findPluginName();
            if (strpos($pathFile, "$namePlugin/Model/") !== false) {
                // Solo añadir ": void" si no existe ya
                if (strpos($fileStr, 'function clear(): void') === false) {
                    $fileStr = str_replace('function clear()', 'function clear(): void', $fileStr);
                }

                // reemplazamos self::$dataBase y static::$dataBase con static::db()
                $fileStr = str_replace(['self::$dataBase', 'static::$dataBase'], 'static::db()', $fileStr);
            }

            // reemplazamos loadFromCode('', $where) por loadWhere($where)
            $fileStr = preg_replace('/->loadFromCode\(\s*[\'\"]\s*\'\s*,\s*([^)]+)\)/', '->loadWhere($1)', $fileStr);

            // reemplazamos loadFromCode($code) por load($code)
            $fileStr = str_replace('->loadFromCode($', '->load($', $fileStr);

            // reemplazamos $this->request->request->get('code', []) por $this->request->request->getArray('codes')
            $fileStr = str_replace('$this->request->request->get(\'code\', [])', '$this->request->request->getArray(\'codes\')', $fileStr);
            $fileStr = str_replace('$this->request->request->get("code", [])', '$this->request->request->getArray("codes")', $fileStr);

            // reemplazamos ->primaryColumnValue() por ->id()
            $fileStr = str_replace('->primaryColumnValue()', '->id()', $fileStr);

            // reemplazamos $this->previousData['xxx'] por $this->getOriginal('xxx')
            $fileStr = preg_replace('/\$this->previousData\[([^\]]+)\]/', '$this->getOriginal($1)', $fileStr);

            // reemplazamos llamadas al método all() con 3 parámetros añadiendo el 4º parámetro (50)
            // añade ", 50" solo cuando hay exactamente 3 argumentos
            $fileStr = preg_replace(
                '/(->|::)all\(\s*([^,()]+)\s*,\s*([^,()]+)\s*,\s*([^,()]+)\s*\)/',
                '$1all($2, $3, $4, 50)',
                $fileStr
            );

            // reemplazamos protected function onChange($field) por protected function onChange(string $field): bool
            $fileStr = str_replace('protected function onChange($field)', 'protected function onChange(string $field): bool', $fileStr);

            // manejamos el archivo Init.php específicamente
            if (basename($pathFile) === 'Init.php') {
                // añadimos tipo de retorno void a init() y update()
                $fileStr = preg_replace('/public function init\(\)(\s*)({)/', 'public function init(): void$1$2', $fileStr);
                $fileStr = preg_replace('/public function update\(\)(\s*)({)/', 'public function update(): void$1$2', $fileStr);

                // verificamos si existe el método uninstall, si no lo añadimos
                if (strpos($fileStr, 'function uninstall()') === false && strpos($fileStr, 'function uninstall(): void') === false) {
                    // buscamos el final de la clase para añadir el método
                    $lastBrace = strrpos($fileStr, '}');
                    if ($lastBrace !== false) {
                        $uninstallMethod = "\n\n    public function uninstall(): void\n    {\n        // código de desinstalación aquí\n    }\n";
                        $fileStr = substr_replace($fileStr, $uninstallMethod, $lastBrace, 0);
                    }
                } else {
                    // si existe, asegurar que tenga el tipo de retorno void
                    $fileStr = str_replace('public function uninstall()', 'public function uninstall(): void', $fileStr);
                }
            }

            // buscamos si se está usando la clase Tools y si la usa, añadimos el use de Tools
            $classToolIsUsed = str_contains($fileStr, 'Tools::');
            $useToolIsPresent = str_contains($fileStr, 'use FacturaScripts\Core\Tools;');
            if ($classToolIsUsed && !$useToolIsPresent) {

                // pueden existir varios use, obtenemos todos los use del core
                $uses = [];
                $matches = [];
                preg_match_all('/use FacturaScripts\\\\Core\\\\[a-zA-Z0-9_\\\\]*;/', $fileStr, $matches);
                foreach ($matches[0] as $match) {
                    $uses[] = $match;
                }

                // añadimos el use de Tools
                $uses[] = 'use FacturaScripts\Core\Tools;';

                // ordenamos los use
                sort($uses);

                // obtenemos la posición del array donde está él use de Tools
                $pos = array_search('use FacturaScripts\Core\Tools;', $uses);

                // obtenemos el namespace del archivo
                $namespace = '';
                preg_match('/namespace FacturaScripts\\\\[a-zA-Z0-9_\\\\]*;/', $fileStr, $matches);
                if (isset($matches[0])) {
                    $namespace = $matches[0];
                }

                // si la posición del use de Tools es 0, añadimos él use después del namespace
                if ($pos === 0) {
                    $fileStr = str_replace($namespace, $namespace . "\n\n" . $uses[$pos], $fileStr);
                } else {
                    // si la posición es mayor que 0, añadimos él use antes de la posición obtenida
                    $fileStr = str_replace($uses[$pos - 1], $uses[$pos - 1] . "\n" . $uses[$pos], $fileStr);
                }
            }

            // guardamos el archivo
            if (file_put_contents($pathFile, $fileStr)) {
                Utils::echo('* ' . $pathFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $pathFile . "\n");
        }
    }

    public static function upgradeTwigFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'twig');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            Utils::echo("* No se han encontrado archivos html.twig.\n");
            return;
        }

        // recorremos la lista de archivos
        foreach ($pathFiles as $pathFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($pathFile);

            // reemplazamos lang
            $fileStr = str_replace('i18n.trans(', 'trans(', $fileStr);

            // reemplazamos settings
            $fileStr = str_replace('appSettings.get(', 'settings(', $fileStr);

            // reemplazamos iconos
            $oldIcons = ['fas ', 'far ', 'fal ', 'fat ', 'fad '];
            $newIcons = ['fa-solid ', 'fa-regular ', 'fa-light ', 'fa-thin ', 'fa-duotone '];
            $fileStr = str_replace($oldIcons, $newIcons, $fileStr);

            // guardamos el archivo
            if (file_put_contents($pathFile, $fileStr)) {
                Utils::echo('* ' . $pathFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $pathFile . "\n");
        }
    }

    public static function upgradeXmlFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'xml');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            Utils::echo("* No se han encontrado archivos xml.\n");
            return;
        }

        // recorremos la lista de archivos
        foreach ($pathFiles as $pathFile) {
            // si la url tiene el texto /table/ lo ignoramos
            if (strpos($pathFile, '/table/') !== false) {
                continue;
            }

            // leemos el contenido del archivo
            $fileStr = file_get_contents($pathFile);

            // reemplazamos iconos
            $oldIcons = ['fas ', 'far ', 'fal ', 'fat ', 'fad '];
            $newIcons = ['fa-solid ', 'fa-regular ', 'fa-light ', 'fa-thin ', 'fa-duotone '];
            $fileStr = str_replace($oldIcons, $newIcons, $fileStr);

            // guardamos el archivo
            if (file_put_contents($pathFile, $fileStr)) {
                Utils::echo('* ' . $pathFile . self::OK);
                continue;
            }

            Utils::echo("* Error al guardar el archivo " . $pathFile . "\n");
        }
    }

    public static function upgradeIniFile(): void
    {
        $iniFile = 'facturascripts.ini';

        // verificamos que el archivo existe
        if (false === file_exists($iniFile)) {
            Utils::echo("* No se encontró el archivo facturascripts.ini.\n");
            return;
        }

        // leemos el contenido del archivo
        $fileContent = file_get_contents($iniFile);

        // parseamos el archivo ini para obtener los valores actuales
        $iniArray = parse_ini_file($iniFile);

        // verificamos si ya tiene min_version definido
        if (isset($iniArray['min_version'])) {
            $currentMinVersion = (float)$iniArray['min_version'];

            // si ya es 2025 o superior, no hacemos nada
            if ($currentMinVersion >= 2025) {
                Utils::echo("* facturascripts.ini ya tiene min_version = " . $currentMinVersion . " (no requiere actualización).\n");
                return;
            }
        }

        // actualizamos min_version a 2025
        $pattern = '/^(\s*min_version\s*=\s*)[\d\.]+(.*)$/m';
        if (preg_match($pattern, $fileContent)) {
            // si existe min_version, lo reemplazamos
            $newContent = preg_replace($pattern, '${1}2025${2}', $fileContent);
        } else {
            // si no existe min_version, lo agregamos después de version
            $versionPattern = '/^(\s*version\s*=\s*[\d\.]+.*\n)/m';
            if (preg_match($versionPattern, $fileContent)) {
                $newContent = preg_replace($versionPattern, '${1}min_version = 2025' . "\n", $fileContent);
            } else {
                // si no hay version, agregamos al final
                $newContent = rtrim($fileContent) . "\nmin_version = 2025\n";
            }
        }

        // guardamos el archivo actualizado
        if (file_put_contents($iniFile, $newContent)) {
            Utils::echo('* ' . $iniFile . self::OK);
        } else {
            Utils::echo("* Error al actualizar el archivo " . $iniFile . "\n");
        }
    }

    private static function getFilesByExtension(string $folder, string $extension, &$files = array()): array
    {
        // obtenemos la lista de archivos y carpetas
        $content = scandir($folder);

        // añadimos las carpetas excluidas
        $excludeDir = array('.', '..', '.git', '.idea', 'vendor', 'node_modules');

        // recorre la lista de archivos
        foreach ($content as $item) {
            // ignorar los directorios excluidos
            if (in_array($item, $excludeDir)) {
                continue;
            }

            // construir la ruta del archivo
            $rute = $folder . '/' . $item;

            // verificar si es un directorio
            if (is_dir($rute)) {
                // llamada recursiva para explorar subcarpetas
                self::getFilesByExtension($rute, $extension, $files);
            } else {
                // verificar la extensión del archivo
                $info = pathinfo($rute);
                if (isset($info['extension']) && strtolower($info['extension']) == strtolower($extension)) {
                    // agregar el archivo al array de resultados
                    $files[] = realpath($rute);
                }
            }
        }

        return $files;
    }

    private static function filterTableFiles(array $files): array
    {
        return array_filter($files, function ($file) {
            // convertir a ruta relativa para verificar más fácilmente
            $relativePath = str_replace(realpath('.') . DIRECTORY_SEPARATOR, '', $file);
            $relativePath = str_replace('\\', '/', $relativePath);

            // excluir archivos en carpetas Table y Extension/Table
            return !preg_match('#(^|/)Table/#', $relativePath) &&
                !preg_match('#(^|/)Extension/Table/#', $relativePath);
        });
    }
}
