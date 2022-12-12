<?php

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

class Columna
{
    /** @var string */
    public $nombre;

    /** @var int */
    public $longitud = 0;

    /** @var int */
    public $maximo;

    /** @var int */
    public $minimo;

    /** bool */
    public $requerido = false;

    /** @var float */
    public $step;

    /** @var string */
    public $tipo;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}