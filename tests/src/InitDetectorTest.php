<?php declare(strict_types=1);

//namespace fsmaker\tests\src;

use fsmaker\InitDetector;
use PHPUnit\Framework\TestCase;

final class InitDetectorTest extends TestCase
{
    private static function getFileContents(string $fileName): string
    {
        $filePath = __DIR__.'/../res/src/InitDetectorTest/'.$fileName;

        $contents = file_get_contents($filePath);
        return is_string($contents) ? $contents : throw new Error('Cannot read '.$filePath);
    }

    // Comprobar si detecta correctamente las existencias
    public function testgetSentenceMatches_1(): void
    {
        $str = self::getFileContents('InitSample.txt');

        $expected = InitDetector::getSentenceMatches(InitDetector::removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 1);
    }

    // Comprobar si detecta correctamente las existencias
    public function testgetSentenceMatches_2(): void
    {
        $str = self::getFileContents('InitSample2.txt');

        $expected = InitDetector::getSentenceMatches(InitDetector::removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 5);
    }

    // Comprobar si detecta correctamente las existencias
    public function testgetSentenceMatches_3(): void
    {
        $str = self::getFileContents('InitSample3.txt');

        $expected = InitDetector::getSentenceMatches(InitDetector::removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 0);
    }

    // Comprobar si detecta correctamente las existencias
    public function testremoveSpaces_(): void
    {
        $str1 = <<<'ENDL'
            esto es un texto
                de prueba para
                comprobar si borra
            los carácteres sobrantes o no.
        ENDL;

        $expected = 'estoesuntextodepruebaparacomprobarsiborraloscarácteressobrantesono.';

        $this->assertSame(InitDetector::removeSpaces($str1), $expected);
        $this->assertSame(InitDetector::removeSpaces($expected), $expected); // por sea caso
        $this->assertSame(InitDetector::removeSpaces("\n"."\t".'   '.'  '), '');
    }

    // Comprobar que detecta cuando un caracter es espacio o no
    public function testisInvisibleChar_(): void
    {
        $this->assertTrue(InitDetector::isInvisibleChar("\n"));
        $this->assertTrue(InitDetector::isInvisibleChar(' '));
        $this->assertTrue(InitDetector::isInvisibleChar('   '));
        $this->assertTrue(InitDetector::isInvisibleChar("\t"));
        $this->assertFalse(InitDetector::isInvisibleChar('a'));
        $this->assertFalse(InitDetector::isInvisibleChar('e'));
        $this->assertFalse(InitDetector::isInvisibleChar('.'));
    }

    public function testInvisibleChar_(): void
}