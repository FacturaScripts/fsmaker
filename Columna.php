<?php
/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

final class Columna
{
    /** @var int */
    public $longitud = 0;

    /** @var int */
    public $maximo = 100;

    /** @var int */
    public $minimo = 0;

    /** @var string */
    public $nombre = '';

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
                    if ($column->tipo === 'serial') {
                        echo "\nYa hay un campo de tipo serial.\n";
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
        $this->longitud = (int)$this->prompt("\nLongitud caracteres") ?? 30;
    }

    public function askMaximo(): void
    {
        $max = (float)$this->prompt("\n¿Valor máximo permitido?, dejar en blanco para no establecer valor.");
        $this->maximo = empty($max) || false === is_numeric($max) ? null : $max;
    }

    public function askMinimo(): void
    {
        $min = (float)$this->prompt("\n¿Valor mínimo permitido?, dejar en blanco para no establecer valor.");
        $this->minimo = empty($min) || false === is_numeric($min) ? null : $min;
    }

    public function askStep(): void
    {
        $step = (float)$this->prompt("\n¿Valor de incremento?, dejar en blanco para no establecer valor.");
        $this->step = empty($step) || false === is_numeric($step) ? null : $step;
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
            $requerido = $this->prompt("\n¿El campo {$this->nombre} es obligatorio? 1=Si, 0=No");
            $this->requerido = $requerido === '1';
        } while ($requerido !== '1' && $requerido !== '0');
    }
}