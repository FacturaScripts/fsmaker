<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class MigrationCommandTest extends CommandTestCase
{
    public function testFallaFueraDePlugin(): void
    {
        $tester = $this->runCommand('migration', ['FixUsuarios']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertFileDoesNotExist('Migration/FixUsuarios.php');
    }

    public function testGeneraMigracionYActualizaInit(): void
    {
        $this->makePluginIni();
        $this->makeInitFile();

        $tester = $this->runCommand('migration', ['FixUsuarios']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        // La migración se ha generado con el namespace y la clase correctos.
        $this->assertFileExists('Migration/FixUsuarios.php');
        $migration = file_get_contents('Migration/FixUsuarios.php');
        $this->assertStringContainsString('namespace FacturaScripts\Plugins\TestPlugin\Migration;', $migration);
        $this->assertStringContainsString('class FixUsuarios extends MigrationClass', $migration);
        // La constante es snake_case + fecha; solo verificamos el prefijo para que sea estable.
        $this->assertStringContainsString("const MIGRATION_NAME = 'fix_usuarios_", $migration);

        // Init.php se ha actualizado con el use y la llamada en update().
        $init = file_get_contents('Init.php');
        $this->assertStringContainsString('use FacturaScripts\Core\Migrations;', $init);
        $this->assertStringContainsString('Migrations::runPluginMigration(new Migration\FixUsuarios());', $init);
    }
}
