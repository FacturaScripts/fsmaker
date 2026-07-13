<?php

namespace fsmaker\Tests;

use fsmaker\ZipGenerator;
use ZipArchive;

/**
 * Tests de ZipGenerator: comprueba que el zip incluye lo que debe,
 * excluye lo que no, y que respeta las reglas de .zipignore.
 *
 * Aprovecha CommandTestCase, que ya prepara un directorio temporal aislado
 * y hace chdir a él, que es todo lo que necesita ZipGenerator::generate().
 */
final class ZipGeneratorTest extends CommandTestCase
{
    /** Crea un plugin mínimo en el directorio temporal actual */
    private function makePlugin(string $name = 'TestZip'): void
    {
        file_put_contents('facturascripts.ini', "name = '" . $name . "'\nversion = 0.1\n");
        mkdir('Model');
        file_put_contents('Init.php', "<?php\n");
        file_put_contents('Model/Producto.php', "<?php\n");
    }

    /** Devuelve la lista de rutas contenidas en el zip */
    private function zipEntries(string $zipName): array
    {
        $this->assertFileExists($zipName);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipName));

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();

        return $entries;
    }

    public function testZipContainsPluginFilesUnderPluginFolder(): void
    {
        $this->makePlugin();
        ZipGenerator::generate();

        $entries = $this->zipEntries('TestZip.zip');
        $this->assertContains('TestZip/Init.php', $entries);
        $this->assertContains('TestZip/Model/Producto.php', $entries);
        $this->assertContains('TestZip/facturascripts.ini', $entries);
    }

    public function testZipExcludesTestFolderHiddenAndJunkFiles(): void
    {
        $this->makePlugin();
        mkdir('Test/main', 0755, true);
        file_put_contents('Test/main/MiTest.php', "<?php\n");
        file_put_contents('.env', 'secreto');
        file_put_contents('Model/.DS_Store', 'basura');
        file_put_contents('Model/Thumbs.db', 'basura');
        file_put_contents('Model/._Producto.php', 'basura');

        ZipGenerator::generate();

        $entries = $this->zipEntries('TestZip.zip');
        $this->assertContains('TestZip/Init.php', $entries);
        $this->assertNotContains('TestZip/Test/main/MiTest.php', $entries);
        $this->assertNotContains('TestZip/.env', $entries);
        $this->assertNotContains('TestZip/Model/.DS_Store', $entries);
        $this->assertNotContains('TestZip/Model/Thumbs.db', $entries);
        $this->assertNotContains('TestZip/Model/._Producto.php', $entries);
    }

    public function testZipIgnoreLiteralGlobAndFolderPatterns(): void
    {
        $this->makePlugin();
        file_put_contents('secret.txt', 'fuera');
        file_put_contents('notas.md', 'fuera');
        mkdir('Docs');
        file_put_contents('Docs/interno.txt', 'fuera');
        file_put_contents('.zipignore', "# comentario\nsecret.txt\n*.md\nDocs/\n");

        ZipGenerator::generate();

        $entries = $this->zipEntries('TestZip.zip');
        $this->assertContains('TestZip/Init.php', $entries);
        $this->assertNotContains('TestZip/secret.txt', $entries);
        $this->assertNotContains('TestZip/notas.md', $entries);
        $this->assertNotContains('TestZip/Docs/interno.txt', $entries);
    }

    public function testZipIgnoreNegationKeepsFile(): void
    {
        $this->makePlugin();
        file_put_contents('LEEME.md', 'dentro');
        file_put_contents('notas.md', 'fuera');
        // la negación debe ir antes del patrón general (gana la primera coincidencia)
        file_put_contents('.zipignore', "!LEEME.md\n*.md\n");

        ZipGenerator::generate();

        $entries = $this->zipEntries('TestZip.zip');
        $this->assertContains('TestZip/LEEME.md', $entries);
        $this->assertNotContains('TestZip/notas.md', $entries);
    }

    public function testZipDoesNotIncludeItselfAndOverwritesPrevious(): void
    {
        $this->makePlugin();
        file_put_contents('TestZip.zip', 'zip viejo');

        ZipGenerator::generate();

        $entries = $this->zipEntries('TestZip.zip');
        $this->assertNotContains('TestZip/TestZip.zip', $entries);
        $this->assertContains('TestZip/Init.php', $entries);
    }

    /** Invoca el método privado shouldIgnore() con los patrones dados; falla si salta algún warning */
    private function shouldIgnore(array $patterns, string $path): bool
    {
        $ref = new \ReflectionClass(ZipGenerator::class);
        $ref->getProperty('ignorePatterns')->setValue(null, $patterns);
        $method = $ref->getMethod('shouldIgnore');

        set_error_handler(function (int $errno, string $errstr): bool {
            $this->fail('Warning inesperado en shouldIgnore(): ' . $errstr);
        });
        try {
            return $method->invoke(null, $path);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Semántica del filtro shouldIgnore(): los patrones sin ruta solo aplican en la raíz,
     * los patrones de directorio van anclados a la raíz y no admiten globs,
     * y en las negaciones gana la primera coincidencia.
     */
    public function testShouldIgnorePatterns(): void
    {
        // literales: coincidencia exacta, solo en raíz
        $this->assertTrue($this->shouldIgnore(['secret.txt'], './secret.txt'));
        $this->assertFalse($this->shouldIgnore(['secret.txt'], './Model/secret.txt'));
        $this->assertFalse($this->shouldIgnore(['secret.txt'], './secret.txt.bak'));

        // glob *: no cruza directorios
        $this->assertTrue($this->shouldIgnore(['*.md'], './notas.md'));
        $this->assertFalse($this->shouldIgnore(['*.md'], './Model/notas.md'));
        $this->assertTrue($this->shouldIgnore(['Model/*.md'], './Model/notas.md'));

        // glob **: cruza directorios
        $this->assertTrue($this->shouldIgnore(['**.md'], './Model/sub/notas.md'));
        $this->assertTrue($this->shouldIgnore(['Model/**'], './Model/sub/notas.md'));

        // glob ?: exactamente un carácter, sin cruzar /
        $this->assertTrue($this->shouldIgnore(['nota?.md'], './notas.md'));
        $this->assertFalse($this->shouldIgnore(['nota?.md'], './nota.md'));

        // directorios: anclados a raíz, sin globs
        $this->assertTrue($this->shouldIgnore(['Docs/'], './Docs/sub/interno.txt'));
        $this->assertFalse($this->shouldIgnore(['Docs/'], './src/Docs/interno.txt'));
        $this->assertFalse($this->shouldIgnore(['Docs/'], './Docsx/interno.txt'));

        // negaciones: gana la primera coincidencia
        $this->assertFalse($this->shouldIgnore(['!LEEME.md', '*.md'], './LEEME.md'));
        $this->assertTrue($this->shouldIgnore(['*.md', '!LEEME.md'], './LEEME.md'));

        // caracteres especiales de regex escapados (incluida la ~, delimitador interno)
        $this->assertTrue($this->shouldIgnore(['file(1).txt'], './file(1).txt'));
        $this->assertTrue($this->shouldIgnore(['ta*~.txt'], './tabla~.txt'));
        $this->assertFalse($this->shouldIgnore(['a.txt'], './aXtxt'));

        // patrones degenerados: no rompen ni matchean
        $this->assertFalse($this->shouldIgnore(['!'], './x.txt'));
        $this->assertFalse($this->shouldIgnore(['/'], './x.txt'));
    }
}
