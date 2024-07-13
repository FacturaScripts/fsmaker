<?php
/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */

namespace fsmaker;

final class FileUpdater
{
    const OK = " -> OK.\n";

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

            // buscamos si existe la palabra toolBox(), ToolBox:: o AppSettings()
            // si no existe, pasamos al siguiente archivo
            if (
                strpos($fileStr, 'ToolBox::') === false &&
                strpos($fileStr, 'toolBox()') === false &&
                strpos($fileStr, 'AppSettings()') === false
            ) {
                continue;
            }

            // reemplazamos log
            $searchLog = [
                'ToolBox::log(', '$this->toolBox()->log(', 'self::toolBox()->log(', 'self::toolBox()::log(',
                'ToolBox::i18nLog(', '$this->toolBox()->i18nLog(', 'self::toolBox()->i18nLog(', 'self::toolBox()::i18nLog('
            ];
            $fileStr = str_replace($searchLog, 'Tools::log(', $fileStr);

            // reemplazamos lang
            $searchLang = [
                'ToolBox::i18n(', '$this->toolBox()->i18n(',
                'self::toolBox()::i18n(', 'self::toolbox()->i18n('
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
