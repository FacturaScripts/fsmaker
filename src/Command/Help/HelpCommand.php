<?php
namespace fsmaker\Command\Help;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\HelpCommand as BaseHelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Helper\DescriptorHelper;

#[AsCommand(
    name: 'help',
    description: 'Muestra este mensaje de ayuda',
)]
class HelpCommand extends BaseHelpCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Muestra este mensaje de ayuda');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandName = $input->getArgument('command_name');

        if ($commandName === 'help' || $commandName === 'list') {
            $helper = new DescriptorHelper();
            $helper->describe($output, $this->getApplication(), [
                'format' => $input->getOption('format'),
                'raw_text' => $input->getOption('raw'),
            ]);

            return 0;
        }

        $this->setCommand($this->getApplication()->find($commandName));

        return parent::execute($input, $output);
    }
}