<?php
/**
 * @author fsmaker
 */

namespace fsmaker\Command\Mod;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;
use fsmaker\InitEditor;

#[AsCommand(
    name: 'calculator-mod',
    description: 'Crea el mod CalculatorMod para recalcular totales'
)]
class CalculatorModCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePlugin()) {
            return Command::FAILURE;
        }

        $name = Utils::findPluginName();
        $dir = "Mod";
        $fileName = $dir . "/CalculatorMod.php";

        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return Command::FAILURE;
        }
        
        Utils::createFolder($dir);

        $samplePath = dirname(__DIR__, 3) . "/samples/CalculatorMod.php.sample";
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        // Escribir en Init.php
        $use = 'use FacturaScripts\Core\Lib\Calculator;';
        $code = 'Calculator::addMod(new Mod\CalculatorMod());';
        $newInitContent = InitEditor::addUse($use);
        InitEditor::setInitContent($newInitContent);
        $newInitContent = InitEditor::addToInitFunction($code, false);
        InitEditor::setInitContent($newInitContent);

        return Command::SUCCESS;
    }
}
