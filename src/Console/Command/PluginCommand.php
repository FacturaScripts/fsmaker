<?php

namespace fsmaker\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

require_once __DIR__ . '/../../../fsmaker.php';

#[AsCommand(
    name: 'plugin',
    description: 'Crear un nuevo plugin'
)]
class PluginCommand extends Command
{
    public function __invoke(): void
    {
        new \fsmaker(['', 'plugin']);
    }
}