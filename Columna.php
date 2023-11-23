<?php
/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

final class Columna
{
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
            echo "\n";
            $type = (int)$this->prompt("Elija el tipo de campo\n"
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
                        echo "\nYa hay un campo de tipo serial o primary key.\n";
                        continue 2;
                    }
                }
            }
            if ($type >= 1 && $type <= 9) {
                $this->setType($type);
                break;
            }

            echo "\nOpción incorrecta.\n";
        }
    }

    public function askLongitud(): void
    {
        $this->longitud = (int)$this->prompt("Longitud caracteres") ?? 30;
    }

    public function askMaximo(): void
    {
        $max = (float)$this->prompt("¿Valor máximo permitido?, dejar en blanco para no establecer valor.");
        $this->maximo = empty($max) && $max != 0 || false === is_numeric($max) ? null : $max;
    }

    public function askMinimo(): void
    {
        $min = (float)$this->prompt("¿Valor mínimo permitido?, dejar en blanco para no establecer valor.");
        $this->minimo = empty($min) && $min != 0 || false === is_numeric($min) ? null : $min;
    }

    public function askStep(): void
    {
        $step = (float)$this->prompt("¿Valor de incremento?, dejar en blanco para no establecer valor.");
        $this->step = empty($step) && $step != 0 || false === is_numeric($step) ? null : $step;
    }

    private function prompt(string $label): string
    {
        echo $label . ': ';
        return trim(fgets(STDIN));
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

        do {
            $display = (int)$this->prompt("¿Cual es la alineación del campo {$this->nombre}? 0=Izquierda, 1=Derecha, 2=Centro, 3=Ocultar");
        } while ($display < 0 || $display > 3);

        switch ($display) {
            case 0:
                $this->display = 'left';
                break;

            case 1:
                $this->display = 'right';
                break;

            case 2:
                $this->display = 'center';
                break;

            case 3:
                $this->display = 'none';
                break;
        }


        do {
            $requerido = $this->prompt("¿El campo {$this->nombre} es obligatorio? 1=Si, 0=No");
            $this->requerido = $requerido === '1';
        } while ($requerido !== '1' && $requerido !== '0');
    }
}