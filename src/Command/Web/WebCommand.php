<?php
namespace fsmaker\Command\Web;

use fsmaker\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'web', description: 'Inicia una interfaz web local')]
class WebCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Puerto', '8787')
            ->addOption('no-open', null, InputOption::VALUE_NONE, 'No abrir navegador');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = (string) $input->getOption('host');
        $port = (int) $input->getOption('port');
        $router = dirname(__DIR__, 3) . '/src/Web/router.php';

        if (!file_exists($router)) {
            $output->writeln('<error>Router no encontrado.</error>');
            return Command::FAILURE;
        }

        $url = "http://$host:$port";
        $output->writeln("<info>Servidor iniciado:</info> <comment>$url</comment> — Ctrl+C para detener.");

        if (!$input->getOption('no-open')) {
            $this->openBrowser($url);
        }

        passthru(sprintf('"%s" -S %s:%d "%s"', PHP_BINARY, $host, $port, $router), $code);

        return $code === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function openBrowser(string $url): void
    {
        $u = escapeshellarg($url);
        match (PHP_OS_FAMILY) {
            'Windows' => @pclose(@popen("start \"\" $u", 'r')),
            'Darwin'  => @shell_exec("open $u > /dev/null 2>&1 &"),
            default   => @shell_exec("xdg-open $u > /dev/null 2>&1 &"),
        };
    }
}