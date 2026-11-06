<?php

namespace fsmaker\Tests;

use fsmaker\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class ModelCommandTest extends CommandTestCase
{
    private function executeModel(array $options): CommandTester
    {
        $tester = new CommandTester((new Application())->find('model'));
        $tester->execute($options);
        return $tester;
    }

    public function testCreaModeloSinPrompts(): void
    {
        $this->makePluginIni();

        $tester = $this->executeModel([
            '--name' => 'Cliente',
            '--table' => 'clientes',
            '--fields' => 'id:serial,nombre:string(50),precio:float',
            '--list-controller' => null,
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileExists('Model/Cliente.php');
        $this->assertFileExists('Table/clientes.xml');
        $this->assertFileExists('Controller/ListCliente.php');
        $this->assertFileExists('XMLView/ListCliente.xml');
        $this->assertFileDoesNotExist('Controller/EditCliente.php');
    }

    public function testCampoInvalidoFalla(): void
    {
        $this->makePluginIni();

        $tester = $this->executeModel([
            '--name' => 'Cliente',
            '--table' => 'clientes',
            '--fields' => 'nombre:foo',
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertFileDoesNotExist('Model/Cliente.php');
    }
}
