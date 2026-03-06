<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Console;

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\TextPrompt;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('FacturaScripts Maker', '2.1.0');
        $this->addCommands($this->getCommands());
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // Fix para windows (la librería prompt no lo soporta)
        // FORZAR CALLBACK SIEMPRE PARA TESTING (Prompt::fallbackWhen(true))
        // En producción debería ser: Prompt::fallbackWhen(PHP_OS_FAMILY === 'Windows');
        Prompt::fallbackWhen(true);

        $io = new SymfonyStyle($input, $output);

        TextPrompt::fallbackUsing(function (TextPrompt $prompt) use ($io) {
            $validator = function ($value) use ($prompt) {
                if ($prompt->validate) {
                    $error = ($prompt->validate)($value ?? '');
                    if (is_string($error) && strlen($error) > 0) {
                        throw new \RuntimeException($error);
                    }
                }
                return $value;
            };

            return (string)$io->ask($prompt->label, $prompt->default, $validator);
        });

        SelectPrompt::fallbackUsing(function (SelectPrompt $prompt) use ($io) {
            $choice = $io->choice($prompt->label, $prompt->options, $prompt->default);
            // Ensure we return the key if options are associative
            if (array_is_list($prompt->options)) {
                return $choice;
            }
            return array_search($choice, $prompt->options) ?: $choice;
        });

        ConfirmPrompt::fallbackUsing(function (ConfirmPrompt $prompt) use ($io) {
            return $io->confirm($prompt->label, $prompt->default);
        });

        MultiSelectPrompt::fallbackUsing(function (MultiSelectPrompt $prompt) use ($io) {
            $default = $prompt->default;
            if (is_array($default) && !empty($default)) {
                $default = implode(',', $default);
            } elseif (empty($default)) {
                $default = null;
            }

            $question = new ChoiceQuestion($prompt->label, $prompt->options, $default);
            $question->setMultiselect(true);

            // Normalizador para permitir separar por espacios además de comas
            $question->setNormalizer(function ($value) {
                if ($value === null) {
                    return $value;
                }
                // Reemplaza uno o más espacios/comas con una sola coma
                return preg_replace('/[\s,]+/', ',', trim($value));
            });

            $result = $io->askQuestion($question);

            if (array_is_list($prompt->options)) {
                return $result;
            }

            // Map values back to keys
            $mapped = [];
            foreach ($result as $val) {
                $key = array_search($val, $prompt->options);
                $mapped[] = $key !== false ? $key : $val;
            }
            return $mapped;
        });

        return parent::doRun($input, $output);
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
