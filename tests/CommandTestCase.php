<?php

namespace fsmaker\Tests;

use fsmaker\Console\Application;
use fsmaker\Utils;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Clase base para los tests de comandos de fsmaker.
 *
 * Centraliza la preparación de un directorio temporal aislado, el silenciado de la salida
 * y la simulación de los prompts interactivos de Laravel Prompts.
 *
 * Los comandos piden datos con Utils::prompt() (Laravel\Prompts\text()). Bajo CommandTester
 * no se ejecuta Application::doRun(), así que registramos aquí un fallback de TextPrompt que
 * devuelve las respuestas pre-encoladas en self::$inputs (en orden), evitando leer de la terminal.
 */
abstract class CommandTestCase extends TestCase
{
    /** @var array<int, string> Cola de respuestas para los prompts de texto */
    protected static array $inputs = [];

    /** @var string Directorio de trabajo original antes del test */
    private string $originalDir;

    /** @var string Directorio temporal aislado donde se ejecuta el comando */
    protected string $testDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->testDir = sys_get_temp_dir() . '/fsmaker_cmd_' . uniqid();
        mkdir($this->testDir, 0755, true);
        chdir($this->testDir);

        // Silenciar la salida para no contaminar la salida de PHPUnit.
        Utils::setSilent(true);

        // Vaciar la cola y activar el modo fallback de los prompts.
        self::$inputs = [];
        Prompt::fallbackWhen(true);
        TextPrompt::fallbackUsing(function (TextPrompt $prompt) {
            return (string) array_shift(self::$inputs);
        });
    }

    protected function tearDown(): void
    {
        chdir($this->originalDir);
        $this->removeDirectory($this->testDir);

        Utils::setSilent(false);
        Prompt::fallbackWhen(false);
    }

    /**
     * Crea el archivo facturascripts.ini para que el directorio sea reconocido como plugin.
     */
    protected function makePluginIni(string $name = 'TestPlugin'): void
    {
        file_put_contents('facturascripts.ini', "name = '" . $name . "'\n");
    }

    /**
     * Crea un Init.php válido a partir del sample, necesario para los comandos que lo modifican.
     */
    protected function makeInitFile(string $name = 'TestPlugin'): void
    {
        $sample = file_get_contents(dirname(__DIR__) . '/samples/Init.php.sample');
        file_put_contents('Init.php', str_replace('[[NAME]]', $name, $sample));
    }

    /**
     * Ejecuta un comando de fsmaker en el directorio temporal con las respuestas indicadas.
     *
     * @param array<int, string> $inputs Respuestas para los prompts de texto, en orden
     */
    protected function runCommand(string $name, array $inputs = []): CommandTester
    {
        self::$inputs = $inputs;

        $application = new Application();
        $command = $application->find($name);
        $tester = new CommandTester($command);
        $tester->execute([]);

        return $tester;
    }

    /**
     * Borra recursivamente un directorio.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
