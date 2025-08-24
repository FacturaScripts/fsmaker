<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

// Usamos clases nativas de PHP para iterar directorios
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RunTests
{
    /**
     * Ejecuta los tests para el plugin actual.
     *
     * @param string|null $fs_folder Ruta a la carpeta raíz de FacturaScripts.
     */
    public static function run(?string $fs_folder = null): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        // si no hay segundo parámetro o no es una ruta que exista, terminamos
        if (empty($fs_folder) || !is_dir($fs_folder)) {
            Utils::echo("* Debes indicar como segundo parámetro la ruta a la carpeta de FacturasScripts.\n");
            Utils::echo("* Ejemplo: fsmaker run-tests ../facturascripts\n");
            return;
        }

        // Aseguramos que la ruta sea absoluta y exista
        $fs_folder_realpath = realpath($fs_folder);
        if ($fs_folder_realpath === false) {
            Utils::echo("* La ruta a FacturasScripts proporcionada no es válida o no existe: " . $fs_folder . "\n");
            return;
        }
        $fs_folder = $fs_folder_realpath;

        // Aseguramos que la ruta termina con el separador de directorios
        if (substr($fs_folder, -1) !== DIRECTORY_SEPARATOR) {
            $fs_folder .= DIRECTORY_SEPARATOR;
        }

        // comprobamos si es el core
        if (!file_exists($fs_folder . 'Core/Kernel.php')) {
            Utils::echo("* La ruta indicada ('" . $fs_folder . "') no corresponde a una instalación de FacturasScripts. Falta el Kernel.\n");
            return;
        } elseif (!file_exists($fs_folder . 'config.php')) {
            Utils::echo("* La ruta indicada ('" . $fs_folder . "') no corresponde a una instalación de FacturasScripts. Falta el config.php.\n");
            return;
        }

        // comprobamos si tiene phpunit y la estructura de Tests necesaria
        if (!file_exists($fs_folder . 'Test/install-plugins.php') || !file_exists($fs_folder . 'vendor/bin/phpunit')) {
            Utils::echo("* La instalación de FacturaScripts no contiene la carpeta Test completa o PHPUnit. Por favor, descarga FacturaScripts mediante git y ejecuta 'composer install' en su raíz.\n");
            return;
        }

        // si este plugin no tiene Tests, terminamos
        if (!is_dir('Test')) {
            Utils::echo("* El plugin no tiene tests unitarios (carpeta 'Test') a ejecutar.\n");
            return;
        }

        // obtenemos las rutas
        $currentPluginName = Utils::findPluginName();
        $currentPluginPath = realpath(getcwd());
        $rawFsPluginPath = $fs_folder . 'Plugins' . DIRECTORY_SEPARATOR . $currentPluginName;
        $fsPluginPath = realpath($rawFsPluginPath);

        if ($fsPluginPath === false) {
            // el plugin no existe en la carpeta de Plugins de FacturaScripts
            self::copyDirectory($currentPluginPath, $rawFsPluginPath);
        } else if ($fsPluginPath !== $currentPluginPath) {
            // la ruta del plugin no está en la carpeta Plugins de FacturaScripts
            self::deleteDirectoryContents($fsPluginPath);
            // Asegurarse de que el directorio base exista después de deleteDirectoryContents si este lo elimina
            if (!is_dir($fsPluginPath)) {
                mkdir($fsPluginPath, 0777, true);
            }
            // copiamos el plugin a la carpeta Plugins
            self::copyDirectory($currentPluginPath, $fsPluginPath);
        }

        $foundTests = false;
        // recorremos las carpetas dentro de Test
        foreach (scandir('Test/') as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $testSubFolder = 'Test' . DIRECTORY_SEPARATOR . $item;
            // si es una carpeta, la ejecutamos
            if (is_dir($testSubFolder)) {
                self::runFolder($testSubFolder, $fs_folder);
                $foundTests = true;
            }
        }
        if (!$foundTests) {
            Utils::echo("* No se encontraron subcarpetas con tests dentro de la carpeta 'Test'.\n");
        }

        // eliminamos el plugin de la carpeta Plugins (si fuese necesario)
        if ($fsPluginPath !== $currentPluginPath) {
            self::deleteDirectoryContents($fsPluginPath);
            if (is_dir($fsPluginPath)) {
                rmdir($fsPluginPath);
            }
        }
    }

    /**
     * Ejecuta los tests definidos en una subcarpeta específica de Test.
     *
     * @param string $test_folder Ruta a la subcarpeta de tests del plugin (ej: Test/BasicTests).
     * @param string $fs_folder Ruta absoluta a la carpeta raíz de FacturaScripts.
     */
    private static function runFolder(string $test_folder, string $fs_folder): void
    {
        // si no hay un archivo install-plugins.txt, terminamos
        if (!file_exists($test_folder . DIRECTORY_SEPARATOR . 'install-plugins.txt')) {
            Utils::echo("* Falta el archivo " . $test_folder . DIRECTORY_SEPARATOR . "install-plugins.txt. Saltando esta carpeta.\n");
            return;
        }

        // Definimos la carpeta destino dentro de la instalación de FacturaScripts
        $dest_folder = $fs_folder . 'Test' . DIRECTORY_SEPARATOR . 'Plugins';

        self::deleteDirectoryContents($dest_folder);

        // Creamos el directorio destino si no existe (deleteDirectoryContents no lo borra)
        if (!is_dir($dest_folder)) {
            mkdir($dest_folder, 0777, true);
        }

        self::copyDirectory($test_folder, $dest_folder);

        // Guardamos el directorio actual para poder volver
        $originalDir = getcwd();
        if ($originalDir === false) {
            Utils::echo("* Error: No se pudo obtener el directorio de trabajo actual.\n");
            return;
        }

        // Cambiamos al directorio de FacturaScripts para ejecutar los comandos
        if (!chdir($fs_folder)) {
            Utils::echo("* Error: No se pudo cambiar al directorio de FacturaScripts: " . $fs_folder . "\n");
            // Intentamos volver al directorio original por si acaso
            chdir($originalDir);
            return;
        }

        $installCommand = 'php Test' . DIRECTORY_SEPARATOR . 'install-plugins.php';
        passthru($installCommand, $installResult);

        if ($installResult !== 0) {
            Utils::echo("* Error durante la ejecución de install-plugins.php (Código de salida: " . $installResult . ").\n");
            // Limpiamos y volvemos al directorio original antes de salir
            Utils::echo("   - Limpiando directorio destino: " . $dest_folder . "\n");
            self::deleteDirectoryContents($dest_folder);
            chdir($originalDir);
            return;
        }

        // Rutas relativas desde $fs_folder
        $phpunitPath = 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit';
        $phpunitConfig = 'phpunit-plugins.xml'; // Archivo de config estándar en FS para tests de plugins

        if (!file_exists($phpunitPath)) {
            Utils::echo("* Error: Ejecutable PHPUnit no encontrado en '" . $fs_folder . $phpunitPath . "'. Ejecuta 'composer install'.\n");
            // Limpiamos y volvemos
            Utils::echo("   - Limpiando directorio destino: " . $dest_folder . "\n");
            self::deleteDirectoryContents($dest_folder);
            chdir($originalDir);
            return;
        }
        if (!file_exists($phpunitConfig)) {
            Utils::echo("* Error: Archivo de configuración '" . $phpunitConfig . "' no encontrado en '" . $fs_folder . "'.\n");
            // Limpiamos y volvemos
            Utils::echo("   - Limpiando directorio destino: " . $dest_folder . "\n");
            self::deleteDirectoryContents($dest_folder);
            chdir($originalDir);
            return;
        }

        $phpunitCommand = $phpunitPath . ' -c ' . $phpunitConfig;
        passthru($phpunitCommand);

        self::deleteDirectoryContents($dest_folder);

        // Volvemos al directorio original
        chdir($originalDir);
    }

    /**
     * Elimina de forma recursiva el contenido de un directorio.
     *
     * @param string $dir Directorio a vaciar.
     */
    private static function deleteDirectoryContents(string $dir): void
    {
        if (!is_dir($dir)) {
            return; // No existe, nada que borrar
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    // Comprobamos si se puede borrar (puede fallar por permisos)
                    if (!@rmdir($file->getRealPath())) {
                        Utils::echo("* Aviso: No se pudo eliminar el directorio: " . $file->getRealPath() . "\n");
                    }
                } else {
                    // Comprobamos si se puede borrar
                    if (!@unlink($file->getRealPath())) {
                        Utils::echo("* Aviso: No se pudo eliminar el archivo: " . $file->getRealPath() . "\n");
                    }
                }
            }
        } catch (Exception $e) {
            Utils::echo("* Error al intentar limpiar el directorio $dir: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Copia de forma recursiva el contenido de un directorio a otro.
     *
     * @param string $source Directorio origen.
     * @param string $dest Directorio destino.
     */
    private static function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            Utils::echo("* Error: El directorio fuente no existe: $source\n");
            return;
        }

        if (!is_dir($dest)) {
            if (!mkdir($dest, 0777, true)) {
                Utils::echo("* Error: No se pudo crear el directorio destino: $dest\n");
                return;
            }
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                try {
                    if ($item->isDir()) {
                        if (!is_dir($destPath)) {
                            if (!mkdir($destPath, 0777, true)) {
                                Utils::echo("* Error: No se pudo crear el subdirectorio destino: $destPath\n");
                                // Podríamos optar por parar aquí o continuar con otros archivos
                                continue;
                            }
                        }
                    } else {
                        if (!copy($item->getRealPath(), $destPath)) {
                            Utils::echo("* Error: No se pudo copiar el archivo: " . $item->getRealPath() . " a " . $destPath . "\n");
                            // Continuar con otros archivos
                            continue;
                        }
                    }
                } catch (Exception $e) {
                    Utils::echo("* Error al procesar " . ($item->isDir() ? "directorio" : "archivo") . " " . $item->getRealPath() . ": " . $e->getMessage() . "\n");
                }
            }
        } catch (Exception $e) {
            Utils::echo("* Error al intentar copiar el directorio $source a $dest: " . $e->getMessage() . "\n");
        }
    }
}
