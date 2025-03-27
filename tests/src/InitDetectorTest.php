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

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');


        $this->assertSame(1, count($result));
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_2(): void
    {
        $str = self::getFileContents('InitSample2.txt');

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame(5, count($result));
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_3(): void
    {
        $str = self::getFileContents('InitSample3.txt');

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame(0, count($result));
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

    // Comprobar que consigue encontrar la localización en un ejemplo real
    public function test_getRealStrPosFromNoSpaceStrPos_1(){

        $str = self::getFileContents('InitSample.txt');

        $expectedWords = 'public function init(): void';
        $expectedMatches = $this->getSentenceMatches($str, $expectedWords);
        $expected = [
            "startPos" => $expectedMatches[0],
            "endPos" => $expectedMatches[0] + mb_strlen($expectedWords),
            "len" => mb_strlen($expectedWords),
            "str" => $expectedWords
        ];
        
        $words = $this->removeSpaces($expectedWords);
        $matches = $this->getSentenceMatches($this->removeSpaces($str), $words);

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen($words));

        $this->assertEquals(json_encode($expected), json_encode($output));
    }

    // Comprobar que encuentra correctamente lo buscado aunque existan similitudes
    public function test_getRealStrPosFromNoSpaceStrPos_2(){

        $str = "string de prueba para ver prueba si de funciona de bien";

        $expectedWords = 'de prueba';
        $expectedMatches = $this->getSentenceMatches($str, $expectedWords);
        $expected = [
            "startPos" => $expectedMatches[0],
            "endPos" => $expectedMatches[0] + mb_strlen($expectedWords),
            "len" => mb_strlen($expectedWords),
            "str" => $expectedWords
        ];
        
        $words = $this->removeSpaces($expectedWords);
        $matches = $this->getSentenceMatches($this->removeSpaces($str), $words);

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen($words));

        $this->assertEquals(json_encode($expected), json_encode($output));
    }

    // Comprobar que no tiene problemas con carácteres raros
    public function test_getRealStrPosFromNoSpaceStrPos_3(){

        $str = "prueba más sencilla";

        $expectedWords = 'más';
        $expectedMatches = $this->getSentenceMatches($str, $expectedWords);
        $expected = [
            "startPos" => $expectedMatches[0],
            "endPos" => $expectedMatches[0] + mb_strlen($expectedWords),
            "len" => mb_strlen($expectedWords),
            "str" => $expectedWords
        ];
        
        $words = $this->removeSpaces($expectedWords);
        $matches = $this->getSentenceMatches($this->removeSpaces($str), $words);

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen($words));

        $this->assertEquals(json_encode($expected), json_encode($output));
    }

    // Comprobar que encuentra correctamente la palabra al final
    public function test_getRealStrPosFromNoSpaceStrPos_4(){

        $str = "esta vez en el final";

        $expectedWords = 'el final';
        $expectedMatches = $this->getSentenceMatches($str, $expectedWords);
        $expected = [
            "startPos" => $expectedMatches[0],
            "endPos" => $expectedMatches[0] + mb_strlen($expectedWords),
            "len" => mb_strlen($expectedWords),
            "str" => $expectedWords
        ];
        
        $words = $this->removeSpaces($expectedWords);
        $matches = $this->getSentenceMatches($this->removeSpaces($str), $words);

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen($words));

        $this->assertEquals(json_encode($expected), json_encode($output));
    }

    // Comprobar que encuentra correctamente la palabra al principio
    public function test_getRealStrPosFromNoSpaceStrPos_5(){

        $str = "aquí sí que está";

        $expectedWords = 'aquí sí';
        $expectedMatches = $this->getSentenceMatches($str, $expectedWords);
        $expected = [
            "startPos" => $expectedMatches[0],
            "endPos" => $expectedMatches[0] + mb_strlen($expectedWords),
            "len" => mb_strlen($expectedWords),
            "str" => $expectedWords
        ];
        
        $words = $this->removeSpaces($expectedWords);
        $matches = $this->getSentenceMatches($this->removeSpaces($str), $words);

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen($words));

        $this->assertEquals(json_encode($expected), json_encode($output));
    }


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

    private function getRealStrPosFromNoSpaceStrPos(string $string, int $noSpacesPos, int $noSpaceWordsLength): mixed
    {
        $getRealStrPosFromNoSpaceStrPos = new ReflectionClass(InitDetector::class)->getMethod('getRealStrPosFromNoSpaceStrPos');

        return $getRealStrPosFromNoSpaceStrPos->invoke(null, $string, $noSpacesPos, $noSpaceWordsLength);
    }
}