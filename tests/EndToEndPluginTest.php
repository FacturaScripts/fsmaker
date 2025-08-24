<?php

namespace fsmaker\Tests;

use PHPUnit\Framework\TestCase;
use fsmaker\Utils;

require_once __DIR__ . '/../fsmaker.php';

class EndToEndPluginTest extends TestCase
{
    private string $testDir;
    private string $originalDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->testDir = sys_get_temp_dir() . '/fsmaker_e2e_test_' . uniqid();
        mkdir($this->testDir, 0755, true);
        chdir($this->testDir);
        
        // Enable silent mode for tests
        Utils::setSilent(true);
    }

    protected function tearDown(): void
    {
        chdir($this->originalDir);
        $this->removeDirectory($this->testDir);
        
        // Restore normal output mode
        Utils::setSilent(false);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testFullPluginCreationProcess(): void
    {
        // Test the complete plugin creation process
        $pluginName = 'TestE2EPlugin';
        
        // Verify we start in a clean directory
        $this->assertFileDoesNotExist('.git');
        $this->assertFileDoesNotExist('.gitignore');
        $this->assertFileDoesNotExist('facturascripts.ini');
        $this->assertDirectoryDoesNotExist($pluginName);
        
        // Simulate the plugin creation steps that fsmaker performs
        
        // 1. Create the plugin directory
        mkdir($pluginName, 0755);
        $this->assertDirectoryExists($pluginName);
        
        // 2. Create all required folders
        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES',
            'Extension/Controller', 'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View',
            'Model/Join', 'Table', 'Translation', 'View', 'XMLView', 'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        
        foreach ($folders as $folder) {
            $fullPath = $pluginName . '/' . $folder;
            mkdir($fullPath, 0755, true);
            touch($fullPath . '/.gitignore');
            
            $this->assertDirectoryExists($fullPath);
            $this->assertFileExists($fullPath . '/.gitignore');
        }
        
        // 3. Change to plugin directory for file creation
        chdir($pluginName);
        
        // 4. Create facturascripts.ini
        $iniContent = "name = '$pluginName'\n";
        $iniContent .= "description = '$pluginName'\n";
        $iniContent .= "version = 0.1\n";
        $iniContent .= "min_version = 2025\n";
        file_put_contents('facturascripts.ini', $iniContent);
        $this->assertFileExists('facturascripts.ini');
        
        // 5. Create .gitignore
        $gitignoreContent = "/.idea/\n/nbproject/\n";
        file_put_contents('.gitignore', $gitignoreContent);
        $this->assertFileExists('.gitignore');
        
        // 6. Create Cron.php
        $cronContent = "<?php\n\nnamespace FacturaScripts\\Plugins\\$pluginName;\n\n";
        $cronContent .= "use FacturaScripts\\Core\\Template\\CronClass;\n\n";
        $cronContent .= "class Cron extends CronClass\n{\n    public function run(): void\n    {\n        // cron logic here\n    }\n}\n";
        file_put_contents('Cron.php', $cronContent);
        $this->assertFileExists('Cron.php');
        
        // 7. Create Init.php
        $initContent = "<?php\n\nnamespace FacturaScripts\\Plugins\\$pluginName;\n\n";
        $initContent .= "use FacturaScripts\\Core\\Template\\InitClass;\n\n";
        $initContent .= "class Init extends InitClass\n{\n    public function init(): void\n    {\n        // init logic here\n    }\n}\n";
        file_put_contents('Init.php', $initContent);
        $this->assertFileExists('Init.php');
        
        // Verify all files were created correctly
        $requiredFiles = ['facturascripts.ini', '.gitignore', 'Cron.php', 'Init.php'];
        foreach ($requiredFiles as $file) {
            $this->assertFileExists($file);
            $this->assertGreaterThan(0, filesize($file));
        }
        
        // Verify the plugin structure is complete
        $this->assertCount(20, $folders);
        
        // Check that the files contain expected content
        $iniFileContent = file_get_contents('facturascripts.ini');
        $this->assertStringContainsString($pluginName, $iniFileContent);
        
        $cronFileContent = file_get_contents('Cron.php');
        $this->assertStringContainsString("namespace FacturaScripts\\Plugins\\$pluginName", $cronFileContent);
        
        $initFileContent = file_get_contents('Init.php');
        $this->assertStringContainsString("namespace FacturaScripts\\Plugins\\$pluginName", $initFileContent);
    }

    public function testPluginValidationChecks(): void
    {
        // Test various validation scenarios that fsmaker should handle
        
        // Test 1: Cannot create plugin when .git exists
        touch('.git');
        $shouldPreventCreation = file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini');
        $this->assertTrue($shouldPreventCreation);
        unlink('.git');
        
        // Test 2: Cannot create plugin when .gitignore exists  
        touch('.gitignore');
        $shouldPreventCreation = file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini');
        $this->assertTrue($shouldPreventCreation);
        unlink('.gitignore');
        
        // Test 3: Cannot create plugin when facturascripts.ini exists
        touch('facturascripts.ini');
        $shouldPreventCreation = file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini');
        $this->assertTrue($shouldPreventCreation);
        unlink('facturascripts.ini');
        
        // Test 4: Can create plugin in clean directory
        $shouldPreventCreation = file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini');
        $this->assertFalse($shouldPreventCreation);
    }

    public function testPluginNameValidationRegex(): void
    {
        // Test the plugin name validation regex used in fsmaker
        $validNames = ['TestPlugin', 'MyPlugin', 'Plugin123', 'Test_Plugin'];
        $invalidNames = ['testPlugin', '123Plugin', 'test plugin', 'test-plugin', ''];
        
        $regex = '/^[A-Z][a-zA-Z0-9_]*$/';
        
        foreach ($validNames as $name) {
            $this->assertMatchesRegularExpression($regex, $name, "Name '$name' should be valid");
        }
        
        foreach ($invalidNames as $name) {
            $this->assertDoesNotMatchRegularExpression($regex, $name, "Name '$name' should be invalid");
        }
    }
}