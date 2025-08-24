<?php
/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */

namespace fsmaker;

final class FileGenerator
{
    const OK = " -> OK.\n";

    public static function createGitIgnore(): void
    {
        $fileName = '.gitignore';
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $template = file_get_contents(__DIR__ . "/../samples/gitignore.sample");
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
    }

    public static function createIni(string $name): void
    {
        $fileName = "facturascripts.ini";
        if (file_exists($fileName)) {
            Utils::echo('* ' . $fileName . " YA EXISTE\n");
            return;
        }

        $sample = file_get_contents(__DIR__ . "/../samples/facturascripts.ini.sample");
        $template = str_replace('[[NAME]]', $name, $sample);
        file_put_contents($fileName, $template);
        Utils::echo('* ' . $fileName . self::OK);
    }

    /**
     * @param string $fileName
     * @param string $tableName
     * @param Column[] $fields
     * @param string $name
     * @param string $namespace
     * @return void
     */
    public static function createModelByFields(string $fileName, string $tableName, array $fields, string $name, string $namespace): void
    {
        $properties = '';
        $primaryColumn = '';
        $clear = '';
        $test = '';
        $save_update = '';

        foreach ($fields as $field) {
            $properties .= $field->getModelProperty();
            $clear .= $field->getModelClear();
            $test .= $field->getModelTest();
            $save_update .= $field->getModelSaveUpdate();

            if ($field->primary) {
                $primaryColumn = $field->nombre;
            }
        }

        $sample = '<?php' . "\n\n"
            . 'namespace FacturaScripts\\' . $namespace . '\Model;' . "\n\n"
            . "use FacturaScripts\Core\Template\ModelClass;\n"
            . "use FacturaScripts\Core\Template\ModelTrait;\n"
            . "use FacturaScripts\Core\Tools;\n"
            . "use FacturaScripts\Core\Session;\n\n"
            . 'class ' . $name . " extends ModelClass\n"
            . "{\n"
            . '    use ModelTrait;' . "\n\n"
            . $properties
            . "    public function clear(): void \n"
            . "    {\n"
            . "        parent::clear();\n"
            . $clear
            . "    }\n\n"
            . "    public static function primaryColumn(): string\n"
            . "    {\n"
            . "        return '" . $primaryColumn . "';\n"
            . '    }' . "\n\n"
            . "    public static function tableName(): string\n"
            . "    {\n"
            . "        return '" . $tableName . "';\n"
            . '    }' . "\n\n"
            . "    public function test(): bool\n"
            . "    {\n"
            . $test . "\n"
            . "        return parent::test();\n"
            . "    }\n";

        if ($save_update) {
            $sample .= "\n"
                . '    protected function saveUpdate(array $values = []): bool' . "\n"
                . "    {\n"
                . $save_update . "\n"
                . '        return parent::saveUpdate($values);' . "\n"
                . "    }\n";
        }

        $sample .= "}\n";

        file_put_contents($fileName, $sample);
    }

    /**
     * @param string $tableFilename
     * @param string $tableName
     * @param Column[] $fields
     * @return void
     */
    public static function createTableXmlByFields(string $tableFilename, string $tableName, array $fields): void
    {
        $columns = '';
        $constraints = '';
        foreach ($fields as $field) {
            $columns .= $field->getTableXmlColumn();
            $constraints .= $field->getTableXmlConstraint($tableName);
        }

        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<table>\n" . $columns . $constraints . "</table>";

        file_put_contents($tableFilename, $sample);
    }

    /**
     * @param string $xmlFilename
     * @param Column[] $fields
     * @param string $type
     * @param bool $extension
     * @return void
     */
    public static function createXMLViewByFields(string $xmlFilename, array $fields, string $type, bool $extension = false): void
    {
        if (empty($fields)) {
            $fields = Column::askMulti($extension);
        }

        // Creamos el xml con los campos introducidos
        $columns = '';
        $last_columns = '';
        $last_fields = ['creation_date', 'last_update', 'nick', 'last_nick'];
        $order = 100;
        $tabs = $type === 'list' ? 8 : 12;

        foreach ($fields as $field) {
            if (in_array($field->nombre, $last_fields)) {
                continue;
            }

            $columns .= $field->getXMLViewColumn($tabs, $order);
            $order += 10;
        }
        // las columnas de creation_date, last_update, nick y last_nick se añaden al final
        foreach ($fields as $field) {
            if (!in_array($field->nombre, $last_fields)) {
                continue;
            }

            $last_columns .= $field->getXMLViewColumn($tabs, $order);
            $order += 10;
        }

        $sample = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<view>\n"
            . "    <columns>\n";

        switch ($type) {
            case 'list': // Es un ListController
                $sample .= $columns . $last_columns;
                break;

            case 'edit': // Es un EditController
                $group_name = $extension ? 'data_extension' : 'data';
                $sample .= '        <group name="' . $group_name . '" numcolumns="12">' . "\n"
                    . $columns
                    . "        </group>\n";
                if ($last_columns) {
                    $sample .= '        <group name="logs" numcolumns="12">' . "\n"
                        . $last_columns
                        . "        </group>\n";
                }
                break;

            default: // No es ninguna de las opciones de antes
                return;
        }

        $sample .= "    </columns>\n"
            . "</view>";

        file_put_contents($xmlFilename, $sample);
    }

    public static function createGithubAction(): void
    {
        if (!Utils::isPluginFolder()) {
            Utils::echo('* No se encuentra en un plugin');
            return;
        }

        $pluginName = Utils::findPluginName();
        if (empty($pluginName)) {
            Utils::echo('* No se pudo obtener el nombre del plugin');
            return;
        }

        $filePath = ".github/workflows/tests.yml";
        if (file_exists($filePath)) {
            Utils::echo('* ' . $filePath . " YA EXISTE\n");
            return;
        }

        if (false === Utils::createFolder(dirname($filePath))) {
            Utils::echo('* No se pudo crear la carpeta ' . dirname($filePath));
            return;
        }

        $template = file_get_contents(__DIR__ . '/../samples/github-action.yml.sample');
        $content = str_replace('$$NOMBRE-DEL-PLUGIN$$', $pluginName, $template);
        if (file_put_contents($filePath, $content)) {
            Utils::echo('* ' . $filePath . self::OK);
        }
    }
}
