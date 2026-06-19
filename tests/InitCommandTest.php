<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class InitCommandTest extends CommandTestCase
{
    public function testFallaSiInitYaExiste(): void
    {
        $this->makePluginIni();
        file_put_contents('Init.php', '<?php // existente');

        $tester = $this->runCommand('init');

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertSame('<?php // existente', file_get_contents('Init.php'));
    }

    public function testGeneraInitPhp(): void
    {
        $this->makePluginIni();

        $tester = $this->runCommand('init');

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileExists('Init.php');

        $init = file_get_contents('Init.php');
        $this->assertStringContainsString('namespace FacturaScripts\Plugins\TestPlugin;', $init);
        $this->assertStringContainsString('class Init extends InitClass', $init);
    }
}
