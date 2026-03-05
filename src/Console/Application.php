<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('FacturaScripts Maker', '2.1.0');
        $this->addCommands($this->getCommands());
    }

    /**
     * Esto es para definir solo las opciones existentes (en fsmaker no existen opciones)
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>list</info> command'),
        ]);
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
            new \fsmaker\Command\Help\HelpCommand(),
            new \fsmaker\Command\Generator\TranslationsCommand(),
            new \fsmaker\Command\Generator\UpgradeCommand(),
            new \fsmaker\Command\Generator\UpgradeBs5Command(),
            new \fsmaker\Command\Generator\ZipCommand(),
            new \fsmaker\Command\Init\InitCommand(),
            new \fsmaker\Command\List\ListCommand(),
            new \fsmaker\Command\Model\ModelCommand(),
            new \fsmaker\Command\Plugin\PluginCommand(),
            new \fsmaker\Command\Test\TestCommand(),
            new \fsmaker\Command\Test\RunTestsCommand(),
            new \fsmaker\Command\View\ViewCommand(),
            new \fsmaker\Command\Worker\WorkerCommand(),
        ];
    }
}
