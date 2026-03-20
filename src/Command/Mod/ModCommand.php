<?php
/**
 * @author Abderrahim Darghal Belkacemi <abdedarghal111@gmail.com>
 */

namespace fsmaker\Command\Mod;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Console\BaseCommand;
use fsmaker\Utils;
use fsmaker\InitEditor;

use function Laravel\Prompts\select;

#[AsCommand(
    name: 'mod',
    description: 'Crea un mod para Modelos (Calculator, HTML Header, Line, Footer)'
)]
class ModCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->requirePlugin()) {
            return Command::FAILURE;
        }

        $option = select(
            label: 'Elija el mod que desea crear',
            options: [
                '1' => 'Calculator',
                '2' => 'SalesHeaderHTML',
                '3' => 'PurchasesHeaderHTML',
                '4' => 'SalesLineHTML',
                '5' => 'PurchasesLineHTML',
                '6' => 'SalesFooterHTML',
                '7' => 'PurchasesFooterHTML'
            ],
            default: '1',
            scroll: 7,
            required: true
        );

        switch ($option) {
            case '1':
                return $this->createMod('CalculatorMod', 'CalculatorMod.php.sample', 'FacturaScripts\Core\Lib\Calculator', 'Calculator::addMod(new Mod\CalculatorMod());');
            case '2':
                return $this->createMod('SalesHeaderHTMLMod', 'SalesHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\SalesHeaderHTML', 'SalesHeaderHTML::addMod(new Mod\SalesHeaderHTMLMod());');
            case '3':
                return $this->createMod('PurchasesHeaderHTMLMod', 'PurchasesHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\PurchasesHeaderHTML', 'PurchasesHeaderHTML::addMod(new Mod\PurchasesHeaderHTMLMod());');
            case '4':
                return $this->createMod('SalesLineHTMLMod', 'SalesLineHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\SalesLineHTML', 'SalesLineHTML::addMod(new Mod\SalesLineHTMLMod());');
            case '5':
                return $this->createMod('PurchasesLineHTMLMod', 'PurchasesLineHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\PurchasesLineHTML', 'PurchasesLineHTML::addMod(new Mod\PurchasesLineHTMLMod());');
            case '6':
                return $this->createMod('SalesFooterHTMLMod', 'SalesHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\SalesFooterHTML', 'SalesFooterHTML::addMod(new Mod\SalesFooterHTMLMod());');
            case '7':
                return $this->createMod('PurchasesFooterHTMLMod', 'PurchasesHTMLMod.php.sample', 'FacturaScripts\Core\Lib\AjaxForms\PurchasesFooterHTML', 'PurchasesFooterHTML::addMod(new Mod\PurchasesFooterHTMLMod());');
        }

        Utils::echo("* Opción no válida.\n");
        return Command::FAILURE;
    }

    private function createMod(string $name, string $sampleName, string $useClass, string $initCode): int
    {
        $dir = "Mod";
        $fileName = $dir . "/" . $name . ".php";

        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return Command::FAILURE;
        }

        Utils::createFolder($dir);

        $samplePath = dirname(__DIR__, 3) . "/samples/" . $sampleName;
        $sample = file_get_contents($samplePath);
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . " -> OK.\n");

        // Escribir en Init.php
        $use = 'use ' . $useClass . ';';
        $newInitContent = InitEditor::addUse($use);
        InitEditor::setInitContent($newInitContent);
        $newInitContent = InitEditor::addToInitFunction($initCode);
        InitEditor::setInitContent($newInitContent);

        return Command::SUCCESS;
    }
}
