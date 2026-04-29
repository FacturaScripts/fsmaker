<?php

namespace fsmaker\Tests;

use fsmaker\FileUpdater;
use fsmaker\Utils;
use PHPUnit\Framework\TestCase;

class FileUpdaterUpgradeTest extends TestCase
{
    public function testUpgradeDataBaseWhere(): void
    {
        $originalDir = getcwd();
        $sandbox = sys_get_temp_dir() . '/fsmaker_upgrade_' . uniqid('', true);

        if (!mkdir($sandbox, 0755, true) && !is_dir($sandbox)) {
            $this->fail('Unable to create temporary plugin directory.');
        }

        try {
            chdir($sandbox);
            Utils::setSilent(true);
            Utils::setFolder($sandbox);

            file_put_contents('facturascripts.ini', "[plugin]\nname = TestPlugin\n");
            mkdir('Model', 0755, true);

            $fixturePath = __DIR__ . '/SampleFiles/DeprecatedDataBaseWhere.txt';
            $targetPath = $sandbox . '/Model/TestModel.php';
            if (!copy($fixturePath, $targetPath)) {
                $this->fail('Unable to copy sample file.');
            }

            FileUpdater::upgradePhpFiles();

            $updated = file_get_contents($targetPath);

            // ninguna referencia a DataBaseWhere debe quedar
            $this->assertStringNotContainsString('DataBaseWhere', $updated);

            // no se usa el constructor genérico: todas son factory methods
            $this->assertStringNotContainsString('new Where(', $updated);

            // el import se actualiza correctamente
            $this->assertStringContainsString('use FacturaScripts\Core\Where;', $updated);

            // los imports no relacionados se conservan
            $this->assertStringContainsString('use FacturaScripts\Core\Tools;', $updated);

            // las 3 llamadas a getSQLWhere se reemplazan por multiSqlLegacy
            $this->assertSame(3, substr_count($updated, 'Where::multiSqlLegacy('));

            // operador = (por defecto, 2 argumentos)
            $this->assertStringContainsString("Where::eq('channel', 'master')", $updated);

            // operadores de comparación numérica
            $this->assertStringContainsString("Where::gt('amount', 100)", $updated);
            $this->assertStringContainsString("Where::lt('stock', 0)", $updated);
            $this->assertStringContainsString("Where::gte('fecha', '2026-01-01')", $updated);
            $this->assertStringContainsString("Where::lte('cantidad', 10)", $updated);
            $this->assertStringContainsString("Where::notEq('estado', 'cancelled')", $updated);

            // IS NULL / IS NOT NULL (el valor null no se pasa como argumento)
            $this->assertStringContainsString("Where::isNull('nick')", $updated);
            $this->assertStringContainsString("Where::isNotNull('telefono1')", $updated);

            // variante OR
            $this->assertStringContainsString("Where::orIsNotNull('telefono2')", $updated);

            // LIKE y XLIKE (value puede ser una llamada a función)
            $this->assertStringContainsString("Where::like('nombre', 'test%')", $updated);
            $this->assertStringContainsString("Where::xlike('descripcion', Tools::noHtml('buscar'))", $updated);

            // IN y NOT IN (value puede contener comas internas en implode)
            $this->assertStringContainsString("Where::in('codimpuesto', implode(',', ['IVA21', 'IVA10']))", $updated);
            $this->assertStringContainsString("Where::notIn('codejercicio', '2024,2025')", $updated);

            // REGEXP
            $this->assertStringContainsString("Where::regexp('codigo', '^-?[0-9]+\$')", $updated);

            // useField (5º argumento true) → encadenado con ->useField()
            $this->assertStringContainsString("Where::lt('stocks.disponible', 'field:stockmin')->useField()", $updated);
            $this->assertStringContainsString("Where::gt('stocks.disponible', 'field:stockmax')->useField()", $updated);

            // rango de fechas con variables (searchByDate)
            $this->assertStringContainsString("Where::gte('fecha', \$dateStart)", $updated);
            $this->assertStringContainsString("Where::lte('fecha', \$dateEnd)", $updated);
        } finally {
            chdir($originalDir);
            Utils::setSilent(false);

            if (is_dir($sandbox)) {
                $items = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sandbox, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $item) {
                    $path = $item->getPathname();
                    $item->isDir() ? rmdir($path) : unlink($path);
                }
                rmdir($sandbox);
            }
        }
    }

    public function testUpgradeAddButton(): void
    {
        $originalDir = getcwd();
        $sandbox = sys_get_temp_dir() . '/fsmaker_upgrade_' . uniqid('', true);

        if (!mkdir($sandbox, 0755, true) && !is_dir($sandbox)) {
            $this->fail('Unable to create temporary plugin directory.');
        }

        try {
            chdir($sandbox);
            Utils::setSilent(true);
            Utils::setFolder($sandbox);

            file_put_contents('facturascripts.ini', "[plugin]\nname = TestPlugin\n");
            mkdir('Controller', 0755, true);

            $fixturePath = __DIR__ . '/SampleFiles/DeprecatedAddButton.txt';
            $targetPath = $sandbox . '/Controller/TestListController.php';
            if (!copy($fixturePath, $targetPath)) {
                $this->fail('Unable to copy sample file.');
            }

            FileUpdater::upgradePhpFiles();

            $updated = file_get_contents($targetPath);

            // ningún $this->addButton( debe quedar
            $this->assertStringNotContainsString('$this->addButton(', $updated);

            // total de llamadas ->addButton( se conserva (4)
            $this->assertSame(4, substr_count($updated, '->addButton('));

            // nombre de vista como variable (2 llamadas)
            $this->assertSame(2, substr_count($updated, '$this->tab($viewName)->addButton('));

            // nombre de vista con comillas simples
            $this->assertStringContainsString('$this->tab(\'OtherView\')->addButton(', $updated);

            // nombre de vista con comillas dobles
            $this->assertStringContainsString('$this->tab("ThirdView")->addButton(', $updated);

            // el contenido de los arrays se conserva intacto
            $this->assertStringContainsString("'action' => 'remove-item'", $updated);
            $this->assertStringContainsString("'confirm' => true", $updated);
            $this->assertStringContainsString("'type' => 'link'", $updated);
            $this->assertStringContainsString("'action' => 'create-accounting-entry'", $updated);
        } finally {
            chdir($originalDir);
            Utils::setSilent(false);

            if (is_dir($sandbox)) {
                $items = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sandbox, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $item) {
                    $path = $item->getPathname();
                    $item->isDir() ? rmdir($path) : unlink($path);
                }
                rmdir($sandbox);
            }
        }
    }

    public function testUpgradeJoinModel(): void
    {
        $originalDir = getcwd();
        $sandbox = sys_get_temp_dir() . '/fsmaker_upgrade_' . uniqid('', true);

        if (!mkdir($sandbox, 0755, true) && !is_dir($sandbox)) {
            $this->fail('Unable to create temporary plugin directory.');
        }

        try {
            chdir($sandbox);
            Utils::setSilent(true);
            Utils::setFolder($sandbox);

            file_put_contents('facturascripts.ini', "[plugin]\nname = TestPlugin\n");
            mkdir('Model', 0755, true);

            $fixturePath = __DIR__ . '/SampleFiles/DeprecatedJoinModel.txt';
            $targetPath = $sandbox . '/Model/TestJoinModel.php';
            if (!copy($fixturePath, $targetPath)) {
                $this->fail('Unable to copy sample file.');
            }

            FileUpdater::upgradePhpFiles();

            $updated = file_get_contents($targetPath);

            // el import antiguo desaparece
            $this->assertStringNotContainsString('use FacturaScripts\Core\Model\Base\JoinModel;', $updated);

            // el import nuevo está presente
            $this->assertStringContainsString('use FacturaScripts\Core\Template\JoinModel;', $updated);

            // el nombre de la clase heredada no cambia
            $this->assertStringContainsString('extends JoinModel', $updated);

            // el import de Where (no relacionado) se conserva
            $this->assertStringContainsString('use FacturaScripts\Core\Where;', $updated);

            // el cuerpo de la clase no se altera
            $this->assertStringContainsString('protected function getTables(): array', $updated);
            $this->assertStringContainsString('protected function getFields(): array', $updated);
            $this->assertStringContainsString('protected function getSQLFrom(): string', $updated);
            $this->assertStringContainsString('INNER JOIN asientos ON asientos.idasiento = partidas.idasiento', $updated);
        } finally {
            chdir($originalDir);
            Utils::setSilent(false);

            if (is_dir($sandbox)) {
                $items = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sandbox, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $item) {
                    $path = $item->getPathname();
                    $item->isDir() ? rmdir($path) : unlink($path);
                }
                rmdir($sandbox);
            }
        }
    }

    public function testUpgradeSalesModSignature(): void
    {
        $originalDir = getcwd();
        $sandbox = sys_get_temp_dir() . '/fsmaker_upgrade_' . uniqid('', true);

        if (!mkdir($sandbox, 0755, true) && !is_dir($sandbox)) {
            $this->fail('Unable to create temporary plugin directory.');
        }

        try {
            chdir($sandbox);
            Utils::setSilent(true);
            Utils::setFolder($sandbox);

            // Configura un plugin mínimo dentro del sandbox
            file_put_contents('facturascripts.ini', "[plugin]\nname = TestPlugin\n");
            mkdir('Mod', 0755, true);

            // Copia el archivo de ejemplo que reproduce el bug original
            $fixturePath = __DIR__ . '/SampleFiles/SalesHeaderHTMLMod.txt';
            $targetPath = $sandbox . '/Mod/SalesHeaderHTMLMod.php';
            if (!copy($fixturePath, $targetPath)) {
                $this->fail('Unable to copy sample file.');
            }

            // Ejecuta la conversión y captura el resultado modificado
            FileUpdater::upgradePhpFiles();

            $updatedContent = file_get_contents($targetPath);
            $this->assertStringNotContainsString('User $user', $updatedContent);
            $this->assertStringContainsString('public function apply(SalesDocument &$model, array $formData): void', $updatedContent);
            $this->assertStringContainsString('public function applyBefore(SalesDocument &$model, array $formData): void', $updatedContent);
            $this->assertStringNotContainsString('@param User $user', $updatedContent);
        } finally {
            chdir($originalDir);
            Utils::setSilent(false);

            // Limpia el sandbox para que no afecte a otros tests
            if (is_dir($sandbox)) {
                $items = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sandbox, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($items as $item) {
                    $path = $item->getPathname();
                    $item->isDir() ? rmdir($path) : unlink($path);
                }

                rmdir($sandbox);
            }
        }
    }
}
