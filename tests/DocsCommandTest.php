<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

/**
 * Tests del comando docs, que crea o completa la estructura de documentación de un plugin o del core.
 */
class DocsCommandTest extends CommandTestCase
{
    public function testFallaFueraDeUnPluginODelCore(): void
    {
        $tester = $this->runCommand('docs');

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertDirectoryDoesNotExist('Doc');
    }

    public function testCreaLaEstructuraCompletaEnUnPlugin(): void
    {
        file_put_contents('facturascripts.ini', "name = 'TestPlugin'\ndescription = 'Gestiona las pruebas'\n");

        $tester = $this->runCommand('docs');

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        foreach (['Doc/User', 'Doc/Dev', 'Doc/Assets'] as $folder) {
            $this->assertFileExists($folder . '/.gitkeep');
        }
        $this->assertSame(['User' => [], 'Dev' => []], json_decode(file_get_contents('Doc/forja.json'), true));
        $this->assertStringContainsString('# TestPlugin: contexto del equipo', file_get_contents('Doc/Context/README.md'));
        $this->assertSame("@AGENTS.md\n", file_get_contents('CLAUDE.md'));

        $agents = file_get_contents('AGENTS.md');
        $this->assertStringStartsWith('Plugin **TestPlugin** para FacturaScripts: Gestiona las pruebas.', $agents);
        $this->assertStringContainsString('(Doc/Context/README.md)', $agents);
        $this->assertStringNotContainsString('[[', $agents);
    }

    public function testCompletaSinSobrescribirLoQueYaExiste(): void
    {
        file_put_contents('facturascripts.ini', "name = 'TestPlugin'\n");
        mkdir('Doc/User', 0755, true);
        file_put_contents('Doc/User/inicio.md', '# Inicio');
        file_put_contents('AGENTS.md', 'Mis instrucciones');
        file_put_contents('CLAUDE.md', 'Instrucciones antiguas');

        $tester = $this->runCommand('docs');

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileDoesNotExist('Doc/User/.gitkeep');
        $this->assertFileExists('Doc/Dev/.gitkeep');
        $this->assertFileExists('Doc/Context/README.md');
        $this->assertSame('Mis instrucciones', file_get_contents('AGENTS.md'));
        $this->assertSame('Instrucciones antiguas', file_get_contents('CLAUDE.md'));
    }

    public function testCreaLaEstructuraEnElCore(): void
    {
        mkdir('Core/Translation', 0755, true);

        $tester = $this->runCommand('docs');

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringStartsWith('Core de **FacturaScripts**', file_get_contents('AGENTS.md'));
        $this->assertStringContainsString('# FacturaScripts: contexto del equipo', file_get_contents('Doc/Context/README.md'));
    }
}
