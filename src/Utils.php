<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

class Utils
{
    public static function findPluginName(): string
    {
        if (self::isPluginFolder()) {
            $ini = parse_ini_file('facturascripts.ini');
            return $ini['name'] ?? '';
        }

        return '';
    }

    public static function getNamespace(): string
    {
        if (self::isCoreFolder()) {
            return 'Core';
        }

        $ini = parse_ini_file('facturascripts.ini');
        return 'Plugins\\' . $ini['name'];
    }

    public static function isCoreFolder(): bool
    {
        return file_exists('Core/Translation') && false === file_exists('facturascripts.ini');
    }

    public static function isPluginFolder(): bool
    {
        return file_exists('facturascripts.ini');
    }

    public static function prompt(string $label, string $pattern = '', string $pattern_explain = ''): ?string
    {
        echo $label . ': ';
        $matches = [];
        $value = trim(fgets(STDIN));

        // si el valor esta vacío, devolvemos null
        if ($value == '') {
            return null;
        }

        if (!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            echo "Valor no válido. Debe " . $pattern_explain . "\n";
            return '';
        }

        return $value;
    }
}
