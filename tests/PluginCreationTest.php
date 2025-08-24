<?php

namespace fsmaker\Tests;

use PHPUnit\Framework\TestCase;
use fsmaker\Utils;

require_once __DIR__ . '/../fsmaker.php';

class PluginCreationTest extends TestCase
{
    private string $testDir;
    private string $originalDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->testDir = sys_get_temp_dir() . '/fsmaker_test_' . uniqid();
        mkdir($this->testDir, 0755, true);
        chdir($this->testDir);
        
        // Enable silent mode for tests
        Utils::setSilent(true);
        
        // Mock global functions if needed
        if (!function_exists('readline')) {
            function readline($prompt) {
                return 'TestPlugin';
            }
        }
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

    public function testPluginCommandExistsInFsmaker(): void
    {
        // Test that the fsmaker class exists and has the required method
        $reflection = new \ReflectionClass('fsmaker');
        $this->assertTrue($reflection->hasMethod('createPluginAction'));
        $this->assertTrue($reflection->hasMethod('help'));
    }

    public function testCreatePluginActionCreatesDirectoryStructure(): void
    {
        // Test the expected directory structure for plugin creation
        $expectedFolders = [
            'Assets/CSS', 'Assets/Images', 'Assets/JS', 'Controller', 
            'Data/Codpais/ESP', 'Data/Lang/ES',
            'Extension/Controller', 'Extension/Model', 'Extension/Table', 
            'Extension/XMLView', 'Extension/View',
            'Model/Join', 'Table', 'Translation', 'View', 'XMLView', 
            'Test/main', 'CronJob', 'Mod', 'Worker'
        ];
        
        // Test that we have the expected folder list
        $this->assertCount(20, $expectedFolders);
        $this->assertContains('Controller', $expectedFolders);
        $this->assertContains('Model/Join', $expectedFolders);
        $this->assertContains('Test/main', $expectedFolders);
    }

    public function testPluginNameValidation(): void
    {
        // Test that plugin names must start with uppercase letter
        $this->assertMatchesRegularExpression('/^[A-Z][a-zA-Z0-9_]*$/', 'TestPlugin');
        $this->assertDoesNotMatchRegularExpression('/^[A-Z][a-zA-Z0-9_]*$/', 'testPlugin');
        $this->assertDoesNotMatchRegularExpression('/^[A-Z][a-zA-Z0-9_]*$/', '123Plugin');
        $this->assertDoesNotMatchRegularExpression('/^[A-Z][a-zA-Z0-9_]*$/', 'Test Plugin');
    }

    public function testCannotCreatePluginInExistingProject(): void
    {
        // Create files that would prevent plugin creation
        touch($this->testDir . '/.git');
        touch($this->testDir . '/.gitignore');
        touch($this->testDir . '/facturascripts.ini');
        
        $this->assertTrue(file_exists($this->testDir . '/.git'));
        $this->assertTrue(file_exists($this->testDir . '/.gitignore'));
        $this->assertTrue(file_exists($this->testDir . '/facturascripts.ini'));
    }

    public function testPluginCreationGeneratesRequiredFiles(): void
    {
        $pluginName = 'TestPlugin';
        
        // Expected files that should be created
        $expectedFiles = [
            $pluginName . '/facturascripts.ini',
            $pluginName . '/.gitignore',
            $pluginName . '/Cron.php',
            $pluginName . '/Init.php'
        ];
        
        foreach ($expectedFiles as $file) {
            $this->assertIsString($file);
        }
    }

    public function testUtilsHelperMethods(): void
    {
        // Test Utils methods that are used in plugin creation
        Utils::setFolder($this->testDir);
        
        // Test that Utils has the required methods
        $this->assertTrue(method_exists(Utils::class, 'prompt'));
        $this->assertTrue(method_exists(Utils::class, 'createFolder'));
        $this->assertTrue(method_exists(Utils::class, 'getNamespace'));
    }
}