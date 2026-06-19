<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class ApiCommandTest extends CommandTestCase
{
    public function testFallaFueraDePlugin(): void
    {
        // Sin facturascripts.ini no es la raíz de un plugin.
        $tester = $this->runCommand('api', ['MiApi', '/api/3/mi-api']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertFileDoesNotExist('Controller/MiApi.php');
    }

    public function testGeneraControladorYActualizaInit(): void
    {
        $this->makePluginIni();
        $this->makeInitFile();

        $tester = $this->runCommand('api', ['MiApi', '/api/3/mi-api']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        // El controlador de API se ha generado con el namespace y la clase correctos.
        $this->assertFileExists('Controller/MiApi.php');
        $controller = file_get_contents('Controller/MiApi.php');
        $this->assertStringContainsString('namespace FacturaScripts\Plugins\TestPlugin\Controller', $controller);
        $this->assertStringContainsString('class MiApi extends ApiController', $controller);

        // Init.php se ha actualizado con los uses y la ruta.
        $init = file_get_contents('Init.php');
        $this->assertStringContainsString('use FacturaScripts\Core\Kernel;', $init);
        $this->assertStringContainsString('use FacturaScripts\Core\Controller\ApiRoot;', $init);
        $this->assertStringContainsString("Kernel::addRoute('/api/3/mi-api'", $init);
        $this->assertStringContainsString("ApiRoot::addCustomResource('mi-api')", $init);
    }
}
