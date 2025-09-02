<?php

namespace fsmaker\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class ApplicationFS extends Application
{
    public function __construct(string $name = 'fsmaker', string $version = '1.9')
    {
        parent::__construct($name, $version);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'El comando a ejecutar'),
//            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>' . $this->defaultCommand . '</info> command'),
//            new InputOption('--silent', null, InputOption::VALUE_NONE, 'Do not output any message'),
//            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Only errors are displayed. All other output is suppressed'),
//            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
//            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
//            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
//            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    protected function getDefaultCommands(): array
    {
        return [
//            new HelpCommand(),
            new ListCommand(),
//            new CompleteCommand(),
//            new DumpCompletionCommand()
        ];
    }
}