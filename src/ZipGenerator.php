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

            // excluimos Thumbs.db
            if ($name === './Thumbs.db') {
                continue;
            }

            // excluimos el propio zip
            if ($name === $zipName) {
                continue;
            }

            $path = str_replace('./', $pluginName . '/', $name);
            $zip->addFile($name, $path);
        }

        $zip->close();
        Utils::echo("* " . $zipName . " -> OK.\n");
    }
}
