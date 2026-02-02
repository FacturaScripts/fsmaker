<?php

namespace fsmaker\Tests;

use fsmaker\FileUpdater;
use fsmaker\Utils;
use PHPUnit\Framework\TestCase;

class FileUpdaterUpgradeTest extends TestCase
{
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
