<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('FacturaScripts Maker', '2.0.0');
        $this->addCommands($this->getCommands());
    }

    private function getCommands(): array
    {
        return [
            new \fsmaker\Command\Api\ApiCommand(),
            new \fsmaker\Command\Controller\ControllerCommand(),
            new \fsmaker\Command\Cron\CronCommand(),
            new \fsmaker\Command\Cron\CronJobCommand(),
            new \fsmaker\Command\Extension\ExtensionCommand(),
            new \fsmaker\Command\Generator\GithubActionCommand(),
            new \fsmaker\Command\Generator\GitignoreCommand(),
            new \fsmaker\Command\Generator\TranslationsCommand(),
            new \fsmaker\Command\Generator\UpgradeCommand(),
            new \fsmaker\Command\Generator\UpgradeBs5Command(),
            new \fsmaker\Command\Generator\ZipCommand(),
            new \fsmaker\Command\Init\InitCommand(),
            new \fsmaker\Command\Model\ModelCommand(),
            new \fsmaker\Command\Plugin\PluginCommand(),
            new \fsmaker\Command\Test\TestCommand(),
            new \fsmaker\Command\Test\RunTestsCommand(),
            new \fsmaker\Command\Worker\WorkerCommand(),
        ];
    }
}
