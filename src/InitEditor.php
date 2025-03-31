<?php
/**
 * @author Abderrahim Darghal Belkacemi <abdedarghal111@gmail.com>
 */

namespace fsmaker;

use ErrorException;

/**
 * InitEditor:
 * - Clase dedicada a ofrecer funciones para detectar y modificar Init.php
 * - Cosas a tener en cuenta:
 *  - Antes de usar esta clase debes de estar en PluginFolder, osea que `fsmaker->isPluginFolder() === true` y estar seguro de que existe Init.php
 * - Modo de funcionar
 *  - Para detectar las cosas, primero remueve los espacios y busca las coincidencias(para evitar que la sintaxis de php sea un problema)
 *  - Después comprueba que las llaves de la función tenga tenga sentido
 *  - Finalmente si no existe la función que se desea agregar, la agrega
 */
class InitEditor {
    const OK = " -> OK.\n";

    private static $INIT_PATH = 'Init.php';

    public static function getInitContent() : string
    {
        $contents = @file_get_contents(self::$INIT_PATH);

        return $contents ? $contents : '';
    }

    public static function setInitContent(string $content) : void
    {
        file_put_contents(self::$INIT_PATH, $content);
        echo '* ' . self::$INIT_PATH . self::OK;
    }

    /**
     * Se encarga de agregar un módulo (use) que le indiques si este no existe dentro.
     * Lo que realiza internamente es buscar el último `use` que encuentre y colocarlo debajo.
     * @param string $str
     * @return string|void
     */
    public static function putUseInstruction(string $useInstruction) : string|null
    {
        $str = self::getInitContent();

        // comprobar si ya está introducido
        $sameUseInstruction = self::getSentenceMatches(self::removeSpaces($str), self::removeSpaces($useInstruction));
        if(count($sameUseInstruction) !== 0){
            // ya existen coincidencias
            return null;
        }

        //escojer el último de todos los use y donde termina
        $matches = self::getSentenceMatches($str, 'use');
        $lastUse = end($matches);
        $endLastUse = strpos($str, "\n", $lastUse) + 1;

        //colocar el nuevo use
        $newStr = mb_substr($str, 0, $endLastUse) . $useInstruction . "\n" . mb_substr($str, $endLastUse);

        return $newStr;
    }

    /**
     * Devuelve el contenido del fichero agregandole la linea introducida por `str`. Si quieres agregarla solo si no existe puedes colocar a true `checkIfNotExists`
     *  - Esta función si que escribe comentarios en la terminal
     * @param string $str la linea de código que se desea insertar
     * @param bool $checkIfNotExists revisa si existe esa linea de código
     * @return string|bool Si no ha sido exitosa la operación devuelve false si no pues el string modificado
     */
    public static function putCodeLineInInitFunction(string $str, bool $checkIfNotExists = false): string|bool
    {
        // obtener el diagnostico general
        $analysis = self::detectValidInitFuntion();
        
        // si algo va mal
        if(!$analysis['isValid']){
            echo $analysis['info'];
            return false;
        }

        $info = $analysis['info'];
        $body = $info['substr'];

        // en caso de estar activo si encuentra una coincidencia cancela la operación
        if($checkIfNotExists){
            $matches = self::getSentenceMatches(self::removeSpaces($body), self::removeSpaces($str));
            if(count($matches) !== 0){
                return false;
            }
        }

        $body .= $str . "\n" . '    ';

        $newStr = mb_substr($info['initContent'], 0, $info['functionStart']) . $body . mb_substr($info['initContent'], $info['functionEnd']);
        
        return $newStr;
    }

    /**
     * Esta función analiza el fichero y detecta si está correcto y se puede agregar funciones. Devuelve información útil.
     * @return array {isValid: bool, info: string|array}
     */
    public static function detectValidInitFuntion() : array
    {
        $str = self::getInitContent();
        
        if($str === ''){
            return [
                'isValid' => false, 
                'info' => '* Error(Init.php): No se ha podido leer Init.php.\n'
            ];
        }
        
        $error = '';
        $words = ['public', 'function', 'init', '(', ')', ':', 'void', '{'];
        
        // comprobar si solo existe una función init
        $matches = self::getSentenceMatches(self::removeSpaces($str), implode($words));

        if(count($matches) !== 1){
            if(count($matches) > 1){
                $error = '* Error(Init.php): Init mal formado. Existe más de una coincidencia de: "public function init(): void{".\n';
            }else{// $matches < 1
                $error = '* Error(Init.php): Init mal formado. No se ha podido encontrar "public function init(): void{".\n';
            }
            return [
                'isValid' => false, 
                'info' => $error
            ];
        }
        
        // Analizar si las tabulaciones están correctas
        $bracesAnalysis = self::getBracesAnalysis($str);

        if($bracesAnalysis['info'] !== 'OK'){
            if($bracesAnalysis['info'] === 'malaSintaxis'){
                $error = '* Error(Init.php): Init mal formado. Hay alguna llave `{` o `}` faltante. Revisa Init.\n';
            }elseif($bracesAnalysis['info'] === 'ordenIncorrecto'){
                $error = '* Error(Init.php): Init mal formado. Las llaves están mal colocadas, revisa si has puesto una llave cerrada `}` antes que una llave abierta `{`.\n';
            }
            return [
                'isValid' => false, 
                'info' => $error
            ];
        }

        // Obtener la posición real en el string de las palabras buscadas
        $realLocationInfo = self::getRealStrPosFromNoSpaceStrPos($str, $matches[0], mb_strlen(implode($words)));
        
        // Obtener el cuerpo de la función encontrando las llaves asignadas.
        $bodyStartPos = $realLocationInfo['endPos'];
        $bodyEndPos = -1;
        $level = 1;
        foreach ($bracesAnalysis['braces'] as $brace) {
            if($brace['position'] < $bodyStartPos){
                continue;
            }

            if($brace['opened']){
                $level++;
            }else{
                $level--;
            }

            if($level === 0){
                $bodyEndPos = $brace['position'] - 1;
                break;
            }
        }

        // echo PHP_EOL.'--------------------------------------------------'.PHP_EOL;
        // echo mb_substr($str, $bodyStartPos, $bodyEndPos - $bodyStartPos);
        // echo PHP_EOL.'--------------------------------------------------'.PHP_EOL;
        return [
            'isValid' => true,
            'info' => [
                'headerData' => $realLocationInfo,
                'bracesData' => $bracesAnalysis,
                'functionStart' => $bodyStartPos,
                'functionEnd' => $bodyEndPos,
                'substr' => mb_substr($str, $bodyStartPos, $bodyEndPos - $bodyStartPos),
                'initContent' => $str
            ]
        ];

    }

    /**
     * Se encarga de recoger las llaves `{}` y realizar un análisis para determinar si están sintacticamente correctas.
     * @param string $str
     * @return array Devuelve una tabla con la info: {info: bool, braces: { array[]{'opened': bool, 'position': int} } }
     */
    private static function getBracesAnalysis(string $str): array
    {
        $openBraces = self::getSentenceMatches($str, '{');
        $closeBraces = self::getSentenceMatches($str, '}');

        // comprobar que existe la misma cantidad de llaves abiertas y cerradas
        if(count($openBraces) !== count($closeBraces)){
            return [
                'info' => 'malaSintaxis',
                'braces' => []
            ];
        }

        // ordenarlos en una misma lista
        $bracesOrdered = [];
        $indexClosed = 0;
        foreach ($openBraces as $position) {
            while($indexClosed < count($closeBraces) && $position > $closeBraces[$indexClosed]){ // va antes
                $bracesOrdered[] = [
                    'opened' => false, 
                    'position' => $closeBraces[$indexClosed]
                ];
                $indexClosed++;
            }

            $bracesOrdered[] = [
                'opened' => true,
                'position' => $position
            ];
        }

        // agregar llaves restantes
        while($indexClosed < count($closeBraces)){
            $bracesOrdered[] = [
                'opened' => false, 
                'position' => $closeBraces[$indexClosed]
            ];
            $indexClosed++;
        }

        // análisis de incongruencia(comprobar que tienen sentido)
        $level = 0;
        foreach ($bracesOrdered as $brace) {
            if($brace['opened']){
                $level++;
            }else{
                $level--;
            }

            if($level <= -1){
                return [
                    'info' => 'ordenIncorrecto',
                    'braces' => $bracesOrdered
                ];
            }
        }

        return [
            'info' => 'OK',
            'braces' => $bracesOrdered
        ];
    }

    /**
     * Esta función lo que hace es recibir la información de la búsqueda sin espacios y realiza la equivalente pero con espacios. Primero se debe comprobar sin espacios si realmente existe la frase buscada.
     * @throws ErrorException, Esta excepción salta si no existe la palabra buscada. Esto sucede si no se han realizado las comprobaciones anteriores por parte del desarrollador. Como por ejemplo que realmente existe la frase que se está buscando dentro del string o si se ha introducido incorrectamente.
     * @param string $string El string donde se realiza la búsqueda de la frase
     * @param int $noSpacesPos La posición del string donde empieza la frase buscada sin espacios(se obtiene con getSentenceMatches)
     * @param int $noSpaceWordsLength El tamaño de la frase buscada sin espacios
     * @return array Devuelve la siguiente tabla autodescriptiva `{'startPos': int, 'endPos' : int, 'len' : int, 'str': string}`
     */
    private static function getRealStrPosFromNoSpaceStrPos(string $string, int $noSpacesPos, int $noSpaceWordsLength): array
    {
        
        $strArr = mb_str_split($string);
        $charsCount = 0;
        $arrPos = 0;
        
        $startPos = -1; // posición real en el string
        $seachedStr = ''; // el string buscado

        
        // loop a todo el texto
        // reset, current, next son funciones para iterar de manera eficiente
        reset($strArr);

        // fase 1: buscar la primera coincidencia
        $currentChar = current($strArr);
        while(is_string($currentChar)){

            if(!self::isInvisibleChar($currentChar)){
                if($charsCount == $noSpacesPos){ // dentro de rango
                    $startPos = $arrPos;
                    break;
                }

                $charsCount++;
            }

            $currentChar = next($strArr);
            $arrPos++;
        }

        if($startPos === -1){
            // esto no debería de saltar, si salta es porque no se han hecho comprobaciones anteriores.
            throw new ErrorException("Error: no se ha encontrado la cadena buscada, compruebe que existe primero.");
        }

        // Fase 2: buscar y encontrar la palabra
        while(is_string($currentChar)){

            $seachedStr .= $currentChar;

            if(!self::isInvisibleChar($currentChar)){

                $charsCount++;

                if($charsCount >= $noSpacesPos + $noSpaceWordsLength){
                    break;
                }
            }
            $arrPos++;
            $currentChar = next($strArr);
        }

        return [
            'startPos' => $startPos,
            'endPos' => $startPos + mb_strlen($seachedStr),
            'len' => mb_strlen($seachedStr),
            'str' => $seachedStr
        ];
    }

    /**
     * Busca las coincidencias en el string y devuelve un array con las posiciones de las coincidencias
     * @param string $str El array a buscar
     * @param string $sentence La frase o string a encontrar
     * @return array<int> Array con las posiciones o vacío si no hay nada
     */
    private static function getSentenceMatches(string $str, string $sentence): array
    {
        $offset = 0;
        $positions = [];
        while (($pos = strpos($str, $sentence, $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    /**
     * Simplemente remueve los espacios de un string
     * @param mixed $str el string en el que remover los espacios
     * @return string es tring sin espacios
     */
    private static function removeSpaces($str): string
    {
        $out = '';
        $content = mb_str_split($str);
        $current = reset($content);

        while(is_string($current)){
            if(!self::isInvisibleChar(current($content))){
                $out .= $current;
            }

            $current = next($content);
        }

        return $out;
    }

    /**
     * Determina si el argumento introducido es un caracter invisible.
     * Son interpretados como carácteres invisibles los siguientes:
     * - Espacios en blanco
     * - Tabulaciones
     * - Saltos de linea
     * @param string $char el carácter a introducir
     * @return bool
     */
    private static function isInvisibleChar(string $char): bool
    {
        return ctype_space($char) || $char === "\n" || $char === "\t" || $char === ' ';
    }
}