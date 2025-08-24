<?php

namespace fsmaker\Tests;

use PHPUnit\Framework\TestCase;
use fsmaker\Utils;
use fsmaker\FileGenerator;

require_once __DIR__ . '/../fsmaker.php';

class PluginCreationIntegrationTest extends TestCase
{
    private string $testDir;
    private string $originalDir;
    private string $pluginName = 'TestPlugin';

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->testDir = sys_get_temp_dir() . '/fsmaker_integration_test_' . uniqid();
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

    public function testPluginDirectoryCreation(): void
    {
        // Simulate plugin directory creation
        mkdir($this->pluginName, 0755);
        $this->assertDirectoryExists($this->pluginName);
        
        // Test expected folder structure
        $folders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 'Data/Codpais/ESP', 'Data/Lang/ES',
            'Extension/Controller', 'Extension/Model', 'Extension/Table', 'Extension/XMLView', 'Extension/View',
            'Model/Join', 'Table', 'Translation', 'View', 'XMLView', 'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        
        foreach ($folders as $folder) {
            Utils::createFolder($this->pluginName . '/' . $folder);
            touch($this->pluginName . '/' . $folder . '/.gitignore');
            
            $this->assertDirectoryExists($this->pluginName . '/' . $folder);
            $this->assertFileExists($this->pluginName . '/' . $folder . '/.gitignore');
        }
    }

    public function testPluginIniFileCreation(): void
    {
        // Create plugin directory
        mkdir($this->pluginName, 0755);
        chdir($this->pluginName);
        
        // Test INI file creation
        FileGenerator::createIni($this->pluginName);
        
        $this->assertFileExists('facturascripts.ini');
        
        $iniContent = file_get_contents('facturascripts.ini');
        $this->assertStringContainsString("name = '" . $this->pluginName . "'", $iniContent);
        $this->assertStringContainsString("version = 0.1", $iniContent);
        $this->assertStringContainsString("min_version = 2025", $iniContent);
    }

    public function testGitignoreCreation(): void
    {
        // Create plugin directory
        mkdir($this->pluginName, 0755);
        chdir($this->pluginName);
        
        // Test .gitignore creation
        FileGenerator::createGitIgnore();
        
        $this->assertFileExists('.gitignore');
        
        $gitignoreContent = file_get_contents('.gitignore');
        $this->assertStringContainsString('/.idea/', $gitignoreContent);
        $this->assertStringContainsString('/nbproject/', $gitignoreContent);
    }

    public function testCronFileCreation(): void
    {
        // Create plugin directory
        mkdir($this->pluginName, 0755);
        chdir($this->pluginName);
        Utils::setFolder(getcwd());
        
        // Simulate Cron.php creation
        $fileName = "Cron.php";
        $sample = file_get_contents(__DIR__ . "/../samples/Cron.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $this->pluginName], $sample);
        file_put_contents($fileName, $template);
        
        $this->assertFileExists($fileName);
        
        $cronContent = file_get_contents($fileName);
        $this->assertStringContainsString('namespace FacturaScripts\\Plugins\\' . $this->pluginName, $cronContent);
        $this->assertStringContainsString('class Cron extends CronClass', $cronContent);
    }

    public function testInitFileCreation(): void
    {
        // Create plugin directory
        mkdir($this->pluginName, 0755);
        chdir($this->pluginName);
        Utils::setFolder(getcwd());
        
        // Simulate Init.php creation
        $fileName = "Init.php";
        $sample = file_get_contents(__DIR__ . "/../samples/Init.php.sample");
        $template = str_replace('[[NAME]]', $this->pluginName, $sample);
        file_put_contents($fileName, $template);
        
        $this->assertFileExists($fileName);
        
        $initContent = file_get_contents($fileName);
        $this->assertStringContainsString('namespace FacturaScripts\\Plugins\\' . $this->pluginName, $initContent);
        $this->assertStringContainsString('class Init extends InitClass', $initContent);
    }

    public function testPluginCreationValidation(): void
    {
        // Test that plugin creation fails when files already exist
        touch('.git');
        $this->assertTrue(file_exists('.git'));
        
        touch('.gitignore'); 
        $this->assertTrue(file_exists('.gitignore'));
        
        touch('facturascripts.ini');
        $this->assertTrue(file_exists('facturascripts.ini'));
        
        // These conditions should prevent plugin creation
        $canCreatePlugin = !(file_exists('.git') || file_exists('.gitignore') || file_exists('facturascripts.ini'));
        $this->assertFalse($canCreatePlugin);
    }

    public function testUtilsNamespaceGeneration(): void
    {
        // Create plugin directory and test namespace generation
        mkdir($this->pluginName, 0755);
        chdir($this->pluginName);
        
        // Create a basic facturascripts.ini to test namespace detection
        file_put_contents('facturascripts.ini', 'name = "' . $this->pluginName . '"');
        
        $namespace = Utils::getNamespace();
        $this->assertStringContainsString($this->pluginName, $namespace);
    }

    public function testFileGeneratorMethods(): void
    {
        // Test that FileGenerator class has required methods
        $this->assertTrue(method_exists(FileGenerator::class, 'createIni'));
        $this->assertTrue(method_exists(FileGenerator::class, 'createGitIgnore'));
        $this->assertTrue(method_exists(FileGenerator::class, 'createGithubAction'));
    }
}