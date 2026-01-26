<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class Utils
{
    /** @var string */
    private static $folder;

    /** @var bool */
    private static $silent = false;

    public static function createFolder(string $path): bool
    {
        if (empty($path) || file_exists($path)) {
            return true;
        }

        if (mkdir($path, 0755, true)) {
            self::echo('* ' . $path . " -> OK.\n");
            return true;
        }

        return false;
    }

    public static function findPluginName(): string
    {
        if (self::isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return $ini['name'] ?? '';
        }

        return '';
    }

    public static function getFolder(): string
    {
        return self::$folder;
    }

    public static function getNamespace(): string
    {
        if (self::isCoreFolder()) {
            return 'Core';
        }

        if (self::isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return 'Plugins\\' . ($ini['name'] ?? '');
        }

        return '';
    }

    public static function isCoreFolder(): bool
    {
        return file_exists('Core/Translation') && false === file_exists('facturascripts.ini');
    }

    public static function isPluginFolder(): bool
    {
        return file_exists('facturascripts.ini');
    }

    /**
     * Muestra un prompt al usuario y solo devuelve el string si cumple con el regex sugerido
     * 
     * Si se coloca allow null, entonces permitirá introducir strings vacíos
     * 
     * Importante: si se quiere que se pueda devolver '', debe poner $allowEmpty a true
     * 
     * @param string $label Enunciado del input
     * @param string $placeholder Placeholder que se muestra al inicio (no se establece en el valor)
     * @param string $default Valor a colocar en el prompt al inicio
     * @param string $hint Texto de ayuda que se muestra debajo del input
     * @param string $regex Expresión regular que debe cumplir el valor introducido para que sea válido. Por defecto, un regex que permite cualquier expresión
     * @param string $errorMessage Mensaje de error que se muestra si el valor no cumple con el regex
     * @param bool $allowEmpty Si se permite que el valor sea una cadena vacía
     * 
     * @return string devuelve el valor si y solo si cumple el regex y no es '' (si se activa $allowEmpty, se permite también)
     */
    public static function prompt(string $label, string $placeholder = '', string $default = '', string $hint = '', string $regex = '/^.*$/', string $errorMessage = '', bool $allowEmpty = false): string {
        return text(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            required: !$allowEmpty, // si se permite null entonces no debe ser required
            validate: function ($value) use ($allowEmpty, $errorMessage, $regex) {
                if ($allowEmpty && $value === '') {
                    // si está permitido devolver nulo y se escribe nulo, entonces devuelve nulo
                    return null;
                }

                $matches = [];
                if (1 !== preg_match($regex, $value, $matches)) {
                    // si no es válido entonces devolver el porqué no es válido
                    return $errorMessage;
                }

                // si es válido devolver null
                return null;
            },
            hint: $hint
        );
    }
    
    /**
     * Muestra un prompt de elegir si o no, devuelve 'Si' o 'No'
     */
    public static function promptYesOrNo(string $label, bool $noPorDefecto = true): string {
        return select(
            label: $label,
            options: [
                // 'valor que devuelve' => 'key que se muestra al usuario a elegir'
                'Si' => 'Si',
                'No' => 'No'
            ],
            default: $noPorDefecto ? 'No' : 'Si',
            scroll: 2, // cantidad de opciones a mostrar a la vez en pantalla (el resto scroll)
            required: true
        );
    }

    /**
     * Convierte CamelCase/PascalCase en kebab-case.
     */
    public static function kebab(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    public static function setFolder(string $folder): void
    {
        self::$folder = $folder;
    }

    /**
     * Sets silent mode for output control (useful in tests).
     */
    public static function setSilent(bool $silent): void
    {
        self::$silent = $silent;
    }

    /**
     * Outputs a message unless silent mode is enabled.
     */
    public static function echo(string $message): void
    {
        if (!self::$silent) {
            echo $message;
        }
    }
}
