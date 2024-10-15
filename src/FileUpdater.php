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
        // Expresiones regulares y sus reemplazos
        $patterns = [
            '/([mp])l(-[0-5])/' => '$1s$2',
            '/([mp])r(-[0-5])/' => '$1e$2',
            '/no-gutters/' => 'g-0',
            '/"close"/' => '"btn-close"',
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

        // actualizamos los archivos php
        foreach ($phpFiles as $phpFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($phpFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($phpFile, $updatedContent)) {
                echo '* ' . $phpFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $phpFile . "\n";
        }

        // actualizamos los archivos twig
        foreach ($twigFiles as $twigFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($twigFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($twigFile, $updatedContent)) {
                echo '* ' . $twigFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $twigFile . "\n";
        }

        // actualizamos los archivos js
        foreach ($jsFiles as $jsFile) {
            // leemos el contenido del archivo
            $fileStr = file_get_contents($jsFile);

            // Reemplazar los patrones encontrados
            $updatedContent = preg_replace(array_keys($patterns), array_values($patterns), $fileStr);

            // guardamos el archivo
            if (file_put_contents($jsFile, $updatedContent)) {
                echo '* ' . $jsFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $jsFile . "\n";
        }
    }

    public static function upgradePhpFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'php');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            echo "* No se han encontrado archivos php.\n";
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
                strpos($fileStr, 'fad ') === false
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

            // buscamos si tiene él use de Tools, si no lo añadimos
            if (strpos($fileStr, 'use FacturaScripts\Core\Tools;') === false) {

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
                    $fileStr = str_replace($namespace, $namespace . "\n\n" . $uses[$pos] . "\n", $fileStr);
                } else {
                    // si la posición es mayor que 0, añadimos él use antes de la posición obtenida
                    $fileStr = str_replace($uses[$pos - 1], $uses[$pos - 1] . "\n" . $uses[$pos] . "\n", $fileStr);
                }
            }

            // guardamos el archivo
            if (file_put_contents($pathFile, $fileStr)) {
                echo '* ' . $pathFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $pathFile . "\n";
        }
    }

    public static function upgradeTwigFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'twig');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            echo "* No se han encontrado archivos html.twig.\n";
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
                echo '* ' . $pathFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $pathFile . "\n";
        }
    }

    public static function upgradeXmlFiles(): void
    {
        // obtenemos la lista de archivos
        $pathFiles = self::getFilesByExtension('.', 'xml');

        // si está vacía, salimos
        if (empty($pathFiles)) {
            echo "* No se han encontrado archivos xml.\n";
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
                echo '* ' . $pathFile . self::OK;
                continue;
            }

            echo "* Error al guardar el archivo " . $pathFile . "\n";
        }
    }

    private static function getFilesByExtension(string $folder, string $extension, &$files = array()): array
    {
        // obtenemos la lista de archivos y carpetas
        $content = scandir($folder);

        // añadimos las carpetas excluidas
        $excludeDir = array('.', '..', '.git', 'idea', 'vendor', 'node_modules');

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
                    $files[] = $rute;
                }
            }
        }

        return $files;
    }
}
