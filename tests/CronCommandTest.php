<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class CronCommandTest extends CommandTestCase
{
    public function testFallaSiCronYaExiste(): void
    {
        $this->makePluginIni();
        file_put_contents('Cron.php', '<?php // existente');

        $tester = $this->runCommand('cron');

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        // No se sobrescribe el archivo existente.
        $this->assertSame('<?php // existente', file_get_contents('Cron.php'));
    }

    public function testGeneraCronPhp(): void
    {
        $this->makePluginIni();

        $tester = $this->runCommand('cron');

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileExists('Cron.php');

        $cron = file_get_contents('Cron.php');
        $this->assertStringContainsString('namespace FacturaScripts\Plugins\TestPlugin;', $cron);
        $this->assertStringContainsString('class Cron extends CronClass', $cron);
    }
}
