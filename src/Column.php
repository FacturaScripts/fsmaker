<?php
/**
 * @author Carlos García Gómez      <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

namespace fsmaker;

final class Column
{
    const FORBIDDEN_WORDS = 'action,activetab,code';

    /** @var string */
    public $display = '';

    /** @var int */
    public $longitud = 0;

    /** @var int */
    public $maximo = 100;

    /** @var int */
    public $minimo = 0;

    /** @var string */
    public $nombre = '';

    /** bool */
    public $primary = false;

    /** bool */
    public $requerido = false;

    /** @var float */
    public $step = 1;

    /** @var string */
    public $tipo = 'integer';

    public function __construct(array $propiedades = [])
    {
        foreach ($propiedades as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function ask(array $previous): void
    {
        while (true) {
            $type = (int)self::prompt("Elija el tipo de campo\n"
                . "1 = serial (autonumérico, ideal para ids)\n"
                . "2 = integer\n"
                . "3 = float\n"
                . "4 = boolean\n"
                . "5 = character varying\n"
                . "6 = text\n"
                . "7 = timestamp\n"
                . "8 = date\n"
                . "9 = time\n");

            if ($type === 1) {
                foreach ($previous as $column) {
                    if ($column->tipo === 'serial' || $column->primary) {
                        Utils::echo("\nYa hay un campo de tipo serial o primary key.\n");
                        continue 2;
                    }
                }
            }
            if ($type >= 1 && $type <= 9) {
                $this->setType($type);
                break;
            }

            Utils::echo("\nOpción incorrecta.\n");
        }
    }

    public static function askMulti(bool $extension = false): array
    {
        $fields = [];

        // si estamos en una extensión, no preguntamos por los campos por defecto
        if (false === $extension) {
            $prompt = self::prompt("¿Desea crear los campos habituales (id, creation_date, last_update, nick,"
                . " last_nick)? 0=No (predeterminado), 1=SI");
            if ($prompt === '1') {
                $fields[] = new Column([
                    'display' => 'none',
                    'nombre' => 'id',
                    'primary' => true,
                    'requerido' => true,
                    'tipo' => 'serial'
                ]);
                $fields[] = new Column([
                    'display' => 'none',
                    'nombre' => 'creation_date',
                    'requerido' => true,
                    'tipo' => 'timestamp'
                ]);
                $fields[] = new Column([
                    'display' => 'none',
                    'nombre' => 'last_update',
                    'tipo' => 'timestamp'
                ]);
                $fields[] = new Column([
                    'display' => 'none',
                    'nombre' => 'nick',
                    'tipo' => 'character varying',
                    'longitud' => 50
                ]);
                $fields[] = new Column([
                    'display' => 'none',
                    'nombre' => 'last_nick',
                    'tipo' => 'character varying',
                    'longitud' => 50
                ]);
                $fields[] = new Column([
                    'nombre' => 'name',
                    'tipo' => 'character varying',
                    'longitud' => 100
                ]);
            }
        }

        while (true) {
            $name = self::prompt("\nNombre del campo (vacío para terminar)", '/^[a-z][a-z0-9_]*$/', 'empezar por letra, solo minúsculas, números o guiones bajos');
            if (is_null($name)) {
                break;
            } elseif (empty($name)) {
                continue;
            }

            if (in_array($name, explode(',', self::FORBIDDEN_WORDS))) {
                Utils::echo("\n" . self::FORBIDDEN_WORDS . " son nombres no permitidos.\n");
                continue;
            }

            $column = new Column(['nombre' => $name]);
            $column->ask($fields);

            $fields[] = $column;
        }

        // ordenamos el array por la propiedad nombre
        usort($fields, function ($a, $b) {
            return strcmp($a->nombre, $b->nombre);
        });

        if (false === $extension) {
            self::askPrimaryKey($fields);
        }

        return $fields;
    }

    public function getModelClear(): string
    {
        if ($this->nombre === 'nick') {
            return "        \$this->" . $this->nombre . " = Session::user()->nick;\n";
        }

        switch ($this->tipo) {
            case 'integer':
                return '        $this->' . $this->nombre . " = 0;\n";

            case 'double precision':
                return '        $this->' . $this->nombre . " = 0.0;\n";

            case 'boolean':
                return '        $this->' . $this->nombre . " = false;\n";

            case 'date':
                return '        $this->' . $this->nombre . " = Tools::date();\n";

            case 'time':
                return '        $this->' . $this->nombre . " = Tools::hour();\n";

            case 'timestamp':
                return '        $this->' . $this->nombre . " = Tools::dateTime();\n";

            default:
                return '';
        }
    }

    public function getModelProperty(): string
    {
        switch ($this->tipo) {
            case 'integer':
            case 'serial':
                return "    /** @var int */\n"
                    . "    public $" . $this->nombre . ";" . "\n\n";

            case 'double precision':
                return "    /** @var float */\n"
                    . "    public $" . $this->nombre . ";" . "\n\n";

            case 'boolean':
                return "    /** @var bool */\n"
                    . "    public $" . $this->nombre . ";" . "\n\n";

            case 'character varying':
            case 'date':
            case 'text':
            case 'time':
            case 'timestamp':
                return "    /** @var string */\n"
                    . "    public $" . $this->nombre . ";" . "\n\n";

            default:
                return '';
        }
    }

    public function getModelSaveUpdate(): string
    {
        if ($this->nombre === 'last_update') {
            return '        $this->last_update = Tools::dateTime();' . "\n";
        }

        if ($this->nombre === 'last_nick') {
            return '        $this->last_nick = Session::user()->nick;' . "\n";
        }

        return '';
    }

    public function getModelTest(): string
    {
        if ($this->nombre === 'creation_date') {
            return '        $this->creation_date = $this->creation_date ?? Tools::dateTime();' . "\n";
        }

        if ($this->nombre === 'nick') {
            return '        $this->nick = $this->nick ?? Session::user()->nick;' . "\n";
        }

        if (in_array($this->nombre, ['last_nick', 'last_update'])) {
            return '';
        }

        switch ($this->tipo) {
            case 'character varying':
            case 'text':
                return '        $this->' . $this->nombre . ' = Tools::noHtml($this->' . $this->nombre . ");\n";

            default:
                return '';
        }
    }

    public function getTableXmlColumn(): string
    {
        $return = "    <column>\n"
            . "        <name>" . $this->nombre . "</name>\n";

        if ($this->tipo === 'character varying') {
            $return .= "        <type>" . $this->tipo . "(" . $this->longitud . ")</type>\n";
        } else {
            $return .= "        <type>" . $this->tipo . "</type>\n";
        }

        if ($this->requerido || $this->primary) {
            $return .= "        <null>NO</null>\n";
        }

        $return .= "    </column>\n";

        return $return;
    }

    public function getTableXmlConstraint(string $table_name): string
    {
        if ($this->primary) {
            return "    <constraint>\n"
                . '        <name>' . $table_name . "_pkey</name>\n"
                . '        <type>PRIMARY KEY (' . $this->nombre . ")</type>\n"
                . "    </constraint>\n";
        }

        $on_delete = $this->requerido ? 'ON DELETE CASCADE' : 'ON DELETE SET NULL';

        switch ($this->nombre) {
            case 'codcliente':
                return "    <constraint>\n"
                    . "        <name>ca_" . $table_name . "_" . $this->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $this->nombre . ") REFERENCES clientes (codcliente) " . $on_delete . " ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";

            case 'codproveedor':
                return "    <constraint>\n"
                    . "        <name>ca_" . $table_name . "_" . $this->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $this->nombre . ") REFERENCES proveedores (codproveedor) " . $on_delete . " ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";

            case 'codserie':
                return "    <constraint>\n"
                    . "        <name>ca_" . $table_name . "_" . $this->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $this->nombre . ") REFERENCES series (codserie) " . $on_delete . " ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";

            case 'idcontacto':
            case 'id_contacto':
                return "    <constraint>\n"
                    . "        <name>ca_" . $table_name . "_" . $this->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $this->nombre . ") REFERENCES contactos (idcontacto) " . $on_delete . " ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";

            case 'last_nick':
            case 'nick':
                return "    <constraint>\n"
                    . "        <name>ca_" . $table_name . "_" . $this->nombre . "</name>\n"
                    . "        <type>FOREIGN KEY (" . $this->nombre . ") REFERENCES users (nick) " . $on_delete . " ON UPDATE CASCADE</type>\n"
                    . "    </constraint>\n";
        }

        return '';
    }

    public function getXMLViewColumn(int $tabs = 4, int $order = 100): string
    {
        $name = str_replace('_', '-', $this->nombre);
        $required = $this->requerido ? ' required="true"' : '';
        $spaces = str_repeat(' ', $tabs);

        switch ($this->nombre) {
            case 'codcliente':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="autocomplete" fieldname="' . $this->nombre . '"' . $required . ">\n"
                    . $spaces . '        <values source="clientes" fieldcode="codcliente" fieldtitle="nombre"/>' . "\n"
                    . $spaces . "    </widget>\n"
                    . $spaces . "</column>\n";

            case 'codproveedor':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="autocomplete" fieldname="' . $this->nombre . '"' . $required . ">\n"
                    . $spaces . '        <values source="proveedores" fieldcode="codproveedor" fieldtitle="nombre"/>' . "\n"
                    . $spaces . "    </widget>\n"
                    . $spaces . "</column>\n";

            case 'codserie':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="select" fieldname="' . $this->nombre . '"' . $required . ">\n"
                    . $spaces . '        <values source="series" fieldcode="codserie" fieldtitle="nombre"/>' . "\n"
                    . $spaces . "    </widget>\n"
                    . $spaces . "</column>\n";

            case 'idcontacto':
            case 'id_contacto':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="autocomplete" fieldname="' . $this->nombre . '"' . $required . ">\n"
                    . $spaces . '        <values source="contactos" fieldcode="idcontacto" fieldtitle="email"/>' . "\n"
                    . $spaces . "    </widget>\n"
                    . $spaces . "</column>\n";

            case 'last_nick':
            case 'nick':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="select" fieldname="' . $this->nombre . '"' . $required . ">\n"
                    . $spaces . '        <values source="users" fieldcode="nick" fieldtitle="nick"/>' . "\n"
                    . $spaces . "    </widget>\n"
                    . $spaces . "</column>\n";
        }

        switch ($this->tipo) {
            default:
                $max_length = is_null($this->longitud) ? '' : ' maxlength="' . $this->longitud . '"';
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $this->nombre . '"' . $max_length . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'serial':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="text" fieldname="' . $this->nombre . '" readonly="true"/>' . "\n"
                    . $spaces . "</column>\n";

            case 'double precision':
            case 'integer':
                $max = is_null($this->maximo) ? '' : ' max="' . $this->maximo . '"';
                $min = is_null($this->minimo) ? '' : ' min="' . $this->minimo . '"';
                $step = is_null($this->step) ? '' : ' step="' . $this->step . '"';
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="number" fieldname="' . $this->nombre . '"' . $max . $min . $step . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'boolean':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="checkbox" fieldname="' . $this->nombre . '"' . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'text':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="textarea" fieldname="' . $this->nombre . '"' . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'timestamp':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="datetime" fieldname="' . $this->nombre . '"' . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'date':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="date" fieldname="' . $this->nombre . '"' . $required . "/>\n"
                    . $spaces . "</column>\n";

            case 'time':
                return $spaces . '<column name="' . $name . '" display="' . $this->display . '" order="' . $order . '">' . "\n"
                    . $spaces . '    <widget type="time" fieldname="' . $this->nombre . '"' . $required . "/>\n"
                    . $spaces . "</column>\n";
        }
    }

    private function askDisplay(): void
    {
        do {
            $display = (int)self::prompt("¿Cual es la alineación del campo {$this->nombre}? 0=Izquierda (predeterminada), 1=Derecha, 2=Centro, 3=Ocultar");
        } while ($display < 0 || $display > 3);

        $displayList = ['left', 'right', 'center', 'none'];
        $this->display = $displayList[$display];
    }

    private function askLongitud(): void
    {
        $long = (int)self::prompt("Longitud caracteres") ?? 30;
        if ($long > 0) {
            $this->longitud = $long;
            return;
        }

        Utils::echo("\nLongitud incorrecta. Se establecerá a 30.\n");
        $this->longitud = 30;
    }

    private function askMaximo(): void
    {
        $max = self::prompt("¿Valor máximo permitido? Deja en blanco para no establecer valor");
        if (is_numeric($max)) {
            $this->maximo = $max;
            return;
        }

        $this->maximo = null;
    }

    private function askMinimo(): void
    {
        $min = self::prompt("¿Valor mínimo permitido? Deja en blanco para no establecer valor");
        if (is_numeric($min)) {
            $this->minimo = $min;
            return;
        }

        $this->minimo = null;
    }

    private static function askPrimaryKey(array &$fields): void
    {
        // si hay un campo serial o primary key, terminamos
        foreach ($fields as $field) {
            if ($field->tipo === 'serial' || $field->primary) {
                return;
            }
        }

        // indicamos que campo es la clave primaria
        while (true) {
            foreach ($fields as $index => $field) {
                Utils::echo($index . " - " . $field->nombre . "\n");
            }

            $pos = self::prompt('No estableció ninguna clave primaria, seleccione una de las anteriores', '/^[0-9]*$/');
            if ($pos == '' || false === isset($fields[$pos])) {
                continue;
            }

            $fields[$pos]->primary = true;
            $fields[$pos]->requerido = true;
            break;
        }
    }

    private function askRequerido(): void
    {
        do {
            $requerido = (int)self::prompt('¿El campo ' . $this->nombre . ' es obligatorio? 0=No (predeterminado), 1=Si');
            $this->requerido = $requerido === 1;
        } while ($requerido !== 1 && $requerido !== 0);
    }

    private function askStep(): void
    {
        $step = self::prompt("¿Valor de incremento? Deja en blanco para no establecer valor");
        if (is_numeric($step)) {
            $this->step = $step;
            return;
        }

        $this->step = null;
    }

    private static function prompt(string $label, string $pattern = '', string $pattern_explain = ''): ?string
    {
        Utils::echo($label . ': ');
        $matches = [];
        $value = trim(fgets(STDIN));

        // si el valor esta vacío, devolvemos null
        if ($value == '') {
            return null;
        }

        if (!empty($pattern) && 1 !== preg_match($pattern, $value, $matches)) {
            Utils::echo("Valor no válido. Debe " . $pattern_explain . "\n");
            return '';
        }

        return $value;
    }

    private function setType(int $type): void
    {
        switch ($type) {
            case 1:
                $this->tipo = 'serial';
                $this->primary = true;
                $this->requerido = true;
                return;

            case 2:
                $this->tipo = 'integer';
                $this->askMaximo();
                $this->askMinimo();
                $this->askStep();
                break;

            case 3:
                $this->tipo = 'double precision';
                $this->askMaximo();
                $this->askMinimo();
                $this->askStep();
                break;

            case 4:
                $this->tipo = 'boolean';
                break;

            case 5:
                $this->tipo = 'character varying';
                $this->askLongitud();
                break;

            case 6:
                $this->tipo = 'text';
                break;

            case 7:
                $this->tipo = 'timestamp';
                break;

            case 8:
                $this->tipo = 'date';
                break;

            case 9:
                $this->tipo = 'time';
                break;
        }

        if ($this->tipo !== 'serial') {
            $this->askRequerido();
        }

        $this->askDisplay();
    }
}
