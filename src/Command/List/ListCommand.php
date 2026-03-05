<?php
namespace fsmaker\Command\List;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\ListCommand as BaseListCommand;

#[AsCommand(
    name: 'list',
    description: 'Muestra este mensaje de ayuda',
)]
class ListCommand extends BaseListCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Muestra este mensaje de ayuda');
    }
}
