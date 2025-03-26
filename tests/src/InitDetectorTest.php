<?php declare(strict_types=1);

//namespace fsmaker\tests\src;

use fsmaker\InitDetector;
use PHPUnit\Framework\TestCase;

final class InitDetectorTest extends TestCase
{
    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_1(): void
    {
        $str = self::getFileContents('InitSample.txt');

        $reflection = new ReflectionClass(InitDetector::class);
        $removeSpaces = $reflection->getMethod('removeSpaces');
        $removeSpaces->setAccessible(true);
        $getSentenceMatches = $reflection->getMethod('getSentenceMatches');
        $getSentenceMatches->setAccessible(true);

        $expected = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 1);
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_2(): void
    {
        $str = self::getFileContents('InitSample2.txt');

        $expected = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 5);
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_3(): void
    {
        $str = self::getFileContents('InitSample3.txt');

        $expected = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame($expected, 0);
    }

    // Comprobar si detecta correctamente las existencias
    public function test_removeSpaces_(): void
    {
        $str1 = <<<'ENDL'
            esto es un texto
                de prueba para
                comprobar si borra
            los carácteres sobrantes o no.
        ENDL;

        $expected = 'estoesuntextodepruebaparacomprobarsiborraloscarácteressobrantesono.';

        $this->assertSame($this->removeSpaces($str1), $expected);
        $this->assertSame($this->removeSpaces($expected), $expected); // por sea caso
        $this->assertSame($this->removeSpaces("\n"."\t".'   '.'  '), '');
    }

    // Comprobar que detecta cuando un caracter es espacio o no
    public function test_isInvisibleChar_(): void
    {
        $this->assertTrue($this->isInvisibleChar("\n"));
        $this->assertTrue($this->isInvisibleChar(' '));
        $this->assertTrue($this->isInvisibleChar('   '));
        $this->assertTrue($this->isInvisibleChar("\t"));
        $this->assertFalse($this->isInvisibleChar('a'));
        $this->assertFalse($this->isInvisibleChar('e'));
        $this->assertFalse($this->isInvisibleChar('.'));
    }

    // Comprobar si detecta correctamente el fichero
//     public function test_detectValidInitFuntion_1(): void
//     {
//         // $staticProperty = $reflection->getProperty('INIT_PATH');
//         // $staticProperty->setAccessible(true);
//         // $staticProperty->setValue(null, 'new_static_value');
//         //$out = InitDetector::detectValidInitFuntion();
//     }


    /*

        Las siguiente funciones que hay debajo de este comentario son solo una ayuda o recorte

    */
    private static function getFileContents(string $fileName): string
    {
        $filePath = __DIR__.'/../res/src/InitDetectorTest/'.$fileName;

        $contents = file_get_contents($filePath);
        return is_string($contents) ? $contents : throw new Error('Cannot read '.$filePath);
    }

    private function removeSpaces(string $char) : mixed
    {
        $removeSpaces = new ReflectionClass(InitDetector::class)->getMethod('removeSpaces');
        //$removeSpaces->setAccessible(true);

        return $removeSpaces->invoke(null, $char);
    }

    private function getSentenceMatches(string $str, string $sentence) : mixed
    {
        $removeSpaces = new ReflectionClass(InitDetector::class)->getMethod('getSentenceMatches');

        return $removeSpaces->invoke(null, $str, $sentence);
    }

    private function isInvisibleChar(string $char): mixed
    {
        $removeSpaces = new ReflectionClass(InitDetector::class)->getMethod('isInvisibleChar');

        return $removeSpaces->invoke(null, $char);
    }
}