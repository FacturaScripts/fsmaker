<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class TestCommandTest extends CommandTestCase
{
    public function testFallaEnCarpetaCore(): void
    {
        // Carpeta core: existe Core/Translation y NO existe facturascripts.ini.
        mkdir('Core/Translation', 0755, true);

        $tester = $this->runCommand('test', ['AccountingPlanTest']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertFileDoesNotExist('Test/main/AccountingPlanTest.php');
    }

    public function testGeneraTestEInstallPlugins(): void
    {
        $this->makePluginIni();

        $tester = $this->runCommand('test', ['AccountingPlanTest']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        // El test se ha generado con el namespace y la clase correctos.
        $this->assertFileExists('Test/main/AccountingPlanTest.php');
        $test = file_get_contents('Test/main/AccountingPlanTest.php');
        // El sample tiene el namespace fijo (no usa el placeholder [[NAME_SPACE]]).
        $this->assertStringContainsString('namespace FacturaScripts\Test\Plugins;', $test);
        $this->assertStringContainsString('final class AccountingPlanTest extends TestCase', $test);

        // El archivo install-plugins.txt se ha creado con el nombre del plugin.
        $this->assertFileExists('Test/main/install-plugins.txt');
        $this->assertSame('TestPlugin', file_get_contents('Test/main/install-plugins.txt'));
    }
}
