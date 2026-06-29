<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipGenerator
{
    private static array $ignorePatterns = [];

    private static function loadZipignore(): void
    {
        self::$ignorePatterns = [];

        if (!file_exists('.zipignore')) {
            return;
        }

        $lines = file('.zipignore', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            self::$ignorePatterns[] = $line;
        }
    }

    private static function shouldIgnore(string $path): bool
    {
        $relativePath = ltrim($path, './\\');

        foreach (self::$ignorePatterns as $pattern) {
            $negated = $pattern[0] === '!';
            if ($negated) {
                $pattern = substr($pattern, 1);
            }

            $pattern = trim($pattern);
            if (empty($pattern)) {
                continue;
            }

            // Directorio: termina en /
            $isDir = substr($pattern, -1) === '/';
            if ($isDir) {
                $pattern = rtrim($pattern, '/');
            }

            // Normalizar el patrón ./ -> vacío
            $pattern = ltrim($pattern, './');

            // Convertir glob a regex
            $regex = self::globToRegex($pattern);

            if ($isDir) {
                // Para directorios, verificar si la ruta comienza con el patrón
                $patternDir = ltrim($pattern, './');
                if (strpos($relativePath, $patternDir . '/') === 0) {
                    return !$negated;
                }
            } else {
                $match = preg_match($regex, $relativePath);
                if ($match) {
                    return !$negated;
                }
            }
        }

        return false;
    }

    private static function globToRegex(string $glob): string
    {
        $regex = preg_quote($glob, '/');
        $regex = str_replace('\*\*', '.*', $regex);
        $regex = str_replace('\*', '[^/]*', $regex);
        $regex = str_replace('\?', '[^/]', $regex);
        return '/^' . $regex . '$/';
    }

    public static function generate(): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $ini = parse_ini_file('facturascripts.ini');
        $pluginName = $ini['name'] ?? '';
        if (empty($pluginName)) {
            Utils::echo("* No se ha encontrado el nombre del plugin.\n");
            return;
        }

        self::loadZipignore();

        $zipName = $pluginName . '.zip';

        if (file_exists($zipName)) {
            unlink($zipName);
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($zipName, ZipArchive::CREATE)) {
            Utils::echo("* Error al crear el archivo zip.\n");
            return;
        }

        $zip->addEmptyDir($pluginName);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.'),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // normalizamos el separador de rutas para Windows
            $name = str_replace('\\', '/', $name);

            // excluimos archivos y carpetas ocultas
            if (substr($name, 0, 3) === './.') {
                continue;
            }

            // excluimos carpetas
            if ($file->isDir()) {
                continue;
            }

            // excluimos la carpeta Test
            if (substr($name, 0, 7) === './Test/') {
                continue;
            }

            // excluimos archivos basura de macOS y Windows en cualquier nivel
            $basename = basename($name);
            if ($basename === '.DS_Store' || $basename === 'Thumbs.db' || $basename === '.AppleDouble' || $basename === '.Spotlight-V100' || $basename === '.Trashes') {
                continue;
            }

            // excluimos la carpeta __MACOSX en cualquier nivel
            if (strpos($name, '/__MACOSX/') !== false || strpos($name, './__MACOSX/') === 0) {
                continue;
            }

            // excluimos archivos AppleDouble (._archivo)
            if (substr($basename, 0, 2) === '._') {
                continue;
            }

            // excluimos el propio zip
            if ($name === './' . $zipName) {
                continue;
            }

            // aplicar reglas de .zipignore
            if (self::shouldIgnore($name)) {
                continue;
            }

            $path = str_replace('./', $pluginName . '/', $name);
            $zip->addFile($name, $path);
        }

        $zip->close();
        Utils::echo("* " . $zipName . " -> OK.\n");
    }
}
