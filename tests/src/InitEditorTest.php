<?php declare(strict_types=1);

//namespace fsmaker\tests\src;

use fsmaker\InitEditor;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InitEditor::class)]

final class InitEditorTest extends TestCase
{
    // Comprobar si detecta correctamente 1 existencia
    public function test_getSentenceMatches_1(): void
    {
        $str = self::getFileContents('InitSample.txt');

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');


        $this->assertSame(1, count($result));
    }

    // Comprobar si detecta correctamente 5 existencias
    public function test_getSentenceMatches_2(): void
    {
        $str = self::getFileContents('InitSample2.txt');

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame(5, count($result));
    }

    // Comprobar si detecta correctamente 0 existencias
    public function test_getSentenceMatches_3(): void
    {
        $str = self::getFileContents('InitSample3.txt');

        $result = $this->getSentenceMatches($this->removeSpaces($str), 'publicfunctioninit():void{');

        $this->assertSame(0, count($result));
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_4(): void
    {
        $str = 'Aquí hay 3 veces 3 y debería de detectar 3 veces eso.';

        $result = $this->getSentenceMatches($str, '3');

        $this->assertSame(3, count($result));
    }

    // Comprobar si detecta correctamente las existencias
    public function test_getSentenceMatches_5(): void
    {
        $str = 'Aquí hay varias llaves({}) de este estilo { y pues espero que detecte { estas existencias {, debería}.';

        $result = $this->getSentenceMatches($str, '{');
        $result2 = $this->getSentenceMatches($str, '}');

        $this->assertSame(4, count($result));
        $this->assertSame(2, count($result2));
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

        $this->assertEquals($expected, $output);
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

        $this->assertEquals($expected, $output);
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

        $this->assertEquals($expected, $output);
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

        $this->assertEquals($expected, $output);
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

        $this->assertEquals($expected, $output);
    }

    // Comprobar casos complejos o donde puede haber fallos(palabras incompletas y carácteres complicados)
    public function test_getRealStrPosFromNoSpaceStrPos_6(){

        $str = "Esta es una prueba con poco sentido debe encontrar aquí y también esto";

        $expectedWords = 'í y tambié';
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

        $this->assertEquals($expected, $output);
    }

    // Comprobar que tira error si realmente funciona mal
    public function test_getRealStrPosFromNoSpaceStrPos_7(){

        $this->expectException(ErrorException::class);

        $str = "aquí no está";

        $output = $this->getRealStrPosFromNoSpaceStrPos($str, 15, mb_strlen("nonoesta"));
    }

    // Comprobar que funciona correctamente la detección
    public function test_getBracesAnalysis_1(){

        $output = $this->getBracesAnalysis("{}{}{}");

        $expected = [
            'info' => 'OK',
            'braces' => [
                ['opened' => true, 'position' => 0],
                ['opened' => false, 'position' => 1],
                ['opened' => true, 'position' => 2],
                ['opened' => false, 'position' => 3],
                ['opened' => true, 'position' => 4],
                ['opened' => false, 'position' => 5],
            ]
        ];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que detecta cuando hay incongruencias de orden
    public function test_getBracesAnalysis_2(){

        $output = $this->getBracesAnalysis("}{}{}{");

        $expected = [
            'info' => 'ordenIncorrecto',
            'braces' => [
                ['opened' => false, 'position' => 0],
                ['opened' => true, 'position' => 1],
                ['opened' => false, 'position' => 2],
                ['opened' => true, 'position' => 3],
                ['opened' => false, 'position' => 4],
                ['opened' => true, 'position' => 5],
            ]
        ];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que detecta cuando están incompletos
    public function test_getBracesAnalysis_3(){

        $output = $this->getBracesAnalysis("}{}{}{}}}}}}");

        $expected = [
            'info' => 'malaSintaxis',
            'braces' => []
        ];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que detecta el cuerpo de la función
    public function test_detectValidInitFuntion_1(){

        $output = $this->detectValidInitFuntion('TestInitFunctionDetection.txt')['info']['substr'];

        $expected = explode('[[Se debe detectar lo siguiente]]', self::getFileContents('TestInitFunctionDetection.txt'))[1];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que devuelve un estado de error cuando se le envía un fichero inexistente.
    public function test_detectValidInitFuntion_2(){

        $output = $this->detectValidInitFuntion('NoExisteEsteFichero.txt');

        $expected = [
            'isValid' => false, 
            'info' => '* Error(Init.php): No se ha podido leer Init.php.\n'
        ];

        $this->assertEquals($expected, $output);
        
    }

    // Comprobar que devuelve un estado de error cuando se encuentra demasiadas coincidencias.
    public function test_detectValidInitFuntion_3(){

        $output = $this->detectValidInitFuntion('InitSample2.txt');

        $expected = [
            'isValid' => false, 
            'info' => '* Error(Init.php): Init mal formado. Existe más de una coincidencia de: "public function init(): void{".\n'
        ];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que devuelve un estado de error cuando se encuentra demasiadas coincidencias.
    public function test_detectValidInitFuntion_4(){

        $output = $this->detectValidInitFuntion('InitSample2.txt');

        $expected = [
            'isValid' => false, 
            'info' => '* Error(Init.php): Init mal formado. Existe más de una coincidencia de: "public function init(): void{".\n'
        ];

        $this->assertEquals($expected, $output);
    }

    // Comprobar que realiza correctamente la función de agregar código al fichero Init.php
    public function test_putCodeLineInInitFunction_1(){

        $output = $this->putCodeLineInInitFunction('Este texto debe estar incluido aquí para la prueba', false, 'InitSample.txt');

        $expected = $this->getFileContents('InitSampleWithExtraFunction.txt');

        $this->assertEquals($expected, $output);
    }

    // Comprobar que cancela cuando ya existe esa linea en Init.php
    public function test_putCodeLineInInitFunction_2(){

        $output = $this->putCodeLineInInitFunction('Este texto debe estar incluido aquí para la prueba', true, 'InitSampleWithExtraFunction.txt');

        $expected = false;

        $this->assertEquals($expected, $output);
    }

    // Comprobar que agrega correctamente el Use a Init.php
    public function test_putUseInstruction_1(){
        $output = $this->putUseInstruction('use FacturaScripts\Core\Template\AnotherClass;', 'TestAddUseCaseSuccesfullBefore.txt');

        $expected = $this->getFileContents('TestAddUseCaseSuccesfull.txt');

        $this->assertEquals($expected, $output);
    }

    // Comprobar que recoge correctamente la "indentation" donde se le indica
    public function test_getCurrentIndentation_1(){
        $output = $this->getCurrentIndentation("   aaaaa", 3);

        $expected = '   ';

        $this->assertEquals($expected, $output);
    }

    // Comprobar que recoge correctamente la "indentation" donde se le indica en un string compuesto
    public function test_getCurrentIndentation_2(){
        $inputText = <<<"TXT"
        Hola, esto es un texto obviamente me lo he inventado pero
        aquí vamos a testear si encuentra correctamente la intentation.
                    Debe de extraerla de aquí.
        TXT;
        
        $output = $this->getCurrentIndentation($inputText, mb_strpos($inputText, 'Debe de extraerla de aquí.'));

        $expected = '            ';

        $this->assertEquals($expected, $output);
    }

    // Comprobar que agrega la indentación correspondiente
    public function test_formatTextWithIndentation_1(){

        $inputText = <<<"TXT"
        En este test debe de agregar la tabulación corresponiente a este texto.
        Será multilinea para
        verificar que funciona como corresponde.
        Veamos si funciona.
        TXT;

        $output = $this->formatTextWithIndentation($inputText, '    ');

        $expected = <<<"TXT"
            En este test debe de agregar la tabulación corresponiente a este texto.
            Será multilinea para
            verificar que funciona como corresponde.
            Veamos si funciona.
        TXT;

        $this->assertEquals($expected, $output);
    }

    // Comprobar que agrega la indentación correspondiente en casos conflictivos
    public function test_formatTextWithIndentation_2(){

        $inputText = <<<"TXT"
        En este test debe de agregar la tabulación corresponiente a este texto.

        TXT;

        $output = $this->formatTextWithIndentation($inputText, '    ');
        
        $expected = <<<"TXT"
            En este test debe de agregar la tabulación corresponiente a este texto.
            
        TXT;

        $this->assertEquals($expected, $output);
    }

    // Comprobar que agrega la indentación correspondiente en casos conflictivos
    public function test_formatTextWithIndentation_3(){

        $inputText = 'En este test debe de agregar la tabulación corresponiente a este texto.';

        $output = $this->formatTextWithIndentation($inputText, '    ');
        
        $expected = '    En este test debe de agregar la tabulación corresponiente a este texto.';

        $this->assertEquals($expected, $output);
    }



    /*

        Las siguiente funciones que hay debajo de este comentario son solo una ayuda o recorte

    */
    private static function getFileContents(string $fileName): string
    {
        $filePath = __DIR__.'/../res/src/InitEditorTest/'.$fileName;

        $contents = file_get_contents($filePath);
        return is_string($contents) ? $contents : throw new Error('Cannot read '.$filePath);
    }

    private function removeSpaces(string $char) : mixed
    {
        $removeSpaces = new ReflectionClass(InitEditor::class)->getMethod('removeSpaces');
        //$removeSpaces->setAccessible(true);

        return $removeSpaces->invoke(null, $char);
    }

    private function getSentenceMatches(string $str, string $sentence) : mixed
    {
        $removeSpaces = new ReflectionClass(InitEditor::class)->getMethod('getSentenceMatches');

        return $removeSpaces->invoke(null, $str, $sentence);
    }

    private function isInvisibleChar(string $char): mixed
    {
        $removeSpaces = new ReflectionClass(InitEditor::class)->getMethod('isInvisibleChar');

        return $removeSpaces->invoke(null, $char);
    }

    private function getRealStrPosFromNoSpaceStrPos(string $string, int $noSpacesPos, int $noSpaceWordsLength): mixed
    {
        $getRealStrPosFromNoSpaceStrPos = new ReflectionClass(InitEditor::class)->getMethod('getRealStrPosFromNoSpaceStrPos');

        return $getRealStrPosFromNoSpaceStrPos->invoke(null, $string, $noSpacesPos, $noSpaceWordsLength);
    }

    private function getBracesAnalysis(string $str): mixed
    {
        $getBracesAnalysis = new ReflectionClass(InitEditor::class)->getMethod('getBracesAnalysis');

        return $getBracesAnalysis->invoke(null, $str);
    }

    private function detectValidInitFuntion(string $fileName): mixed
    {
        $reflection = new ReflectionClass(InitEditor::class);

        $staticProperty = $reflection->getProperty('INIT_PATH');
        //$staticProperty->setAccessible(true);
        $staticProperty->setValue(null, __DIR__.'/../res/src/InitEditorTest/'.$fileName);

        $detectValidInitFuntion = $reflection->getMethod('detectValidInitFuntion');

        return $detectValidInitFuntion->invoke(null);
    }

    private function putCodeLineInInitFunction(string $str, bool $bool, string $fileName): mixed
    {
        $reflection = new ReflectionClass(InitEditor::class);

        $staticProperty = $reflection->getProperty('INIT_PATH');
        //$staticProperty->setAccessible(true);
        $staticProperty->setValue(null, __DIR__.'/../res/src/InitEditorTest/'.$fileName);

        $putCodeLineInInitFunction = $reflection->getMethod('putCodeLineInInitFunction');

        return $putCodeLineInInitFunction->invoke(null, $str, $bool);
    }

    private function putUseInstruction(string $str, string $fileName): mixed
    {
        $reflection = new ReflectionClass(InitEditor::class);

        $staticProperty = $reflection->getProperty('INIT_PATH');
        //$staticProperty->setAccessible(true);
        $staticProperty->setValue(null, __DIR__.'/../res/src/InitEditorTest/'.$fileName);

        $putUseInstruction = $reflection->getMethod('putUseInstruction');

        return $putUseInstruction->invoke(null, $str);
    }

    private function getCurrentIndentation(string $str, int $indentEndPos): mixed
    {
        $getCurrentIndentation = new ReflectionClass(InitEditor::class)->getMethod('getCurrentIndentation');

        return $getCurrentIndentation->invoke(null, $str, $indentEndPos);
    }

    private function formatTextWithIndentation(string $str, string $indention): mixed
    {
        $formatTextWithIndentation = new ReflectionClass(InitEditor::class)->getMethod('formatTextWithIndentation');

        return $formatTextWithIndentation->invoke(null, $str, $indention);
    }
}