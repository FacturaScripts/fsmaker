<?php

namespace fsmaker\Tests;

use Symfony\Component\Console\Command\Command;

class CronJobCommandTest extends CommandTestCase
{
    public function testActualizaCronPhpExistente(): void
    {
        $this->makePluginIni();

        // Cron.php pre-existente desde el sample.
        $sample = file_get_contents(dirname(__DIR__) . '/samples/Cron.php.sample');
        file_put_contents('Cron.php', str_replace(['[[NAME_SPACE]]', '[[NAME]]'], ['Plugins\TestPlugin', 'TestPlugin'], $sample));

        $tester = $this->runCommand('cronjob', ['OtroJob']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileExists('CronJob/OtroJob.php');

        $cron = file_get_contents('Cron.php');
        $this->assertStringContainsString('use FacturaScripts\Plugins\TestPlugin\CronJob\OtroJob;', $cron);
        $this->assertStringContainsString('$this->job(OtroJob::JOB_NAME)', $cron);
    }

    public function testFallaSiCronJobYaExiste(): void
    {
        $this->makePluginIni();
        mkdir('CronJob');
        file_put_contents('CronJob/MiCronJob.php', '<?php // existente');

        $tester = $this->runCommand('cronjob', ['MiCronJob']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertSame('<?php // existente', file_get_contents('CronJob/MiCronJob.php'));
    }

    public function testGeneraCronJobYCreaCronPhp(): void
    {
        $this->makePluginIni();

        $tester = $this->runCommand('cronjob', ['MiCronJob']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        // El CronJob se ha generado con el JOB_NAME en kebab-case y el namespace correcto.
        $this->assertFileExists('CronJob/MiCronJob.php');
        $cronJob = file_get_contents('CronJob/MiCronJob.php');
        $this->assertStringContainsString('namespace FacturaScripts\Plugins\TestPlugin\CronJob;', $cronJob);
        $this->assertStringContainsString('class MiCronJob extends CronJobClass', $cronJob);
        $this->assertStringContainsString("const JOB_NAME = 'mi-cron-job';", $cronJob);

        // Cron.php se ha creado y registra el nuevo job.
        $this->assertFileExists('Cron.php');
        $cron = file_get_contents('Cron.php');
        $this->assertStringContainsString('use FacturaScripts\Plugins\TestPlugin\CronJob\MiCronJob;', $cron);
        $this->assertStringContainsString('$this->job(MiCronJob::JOB_NAME)', $cron);
    }
}
