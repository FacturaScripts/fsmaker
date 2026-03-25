# Guía para Migrar Modelos de FacturaScripts 2017 a 2018

> **ID:** 707 | **Permalink:** migrar-los-modelos-924 | **Última modificación:** 19-03-2025
> **URL oficial:** https://facturascripts.com/migrar-los-modelos-924

Desde FacturaScripts 2018, hemos adoptado [espacios de nombres, autoloading y notación CamelCase](https://facturascripts.com/publicaciones/antes-de-empezar-a-programar-580). Por ello, es necesario renombrar tus modelos siguiendo estas nuevas pautas: **la primera letra del nombre en mayúscula y sin guiones bajos.**

## Procedimiento de Renombrado

### Ejemplo Antes de la Migración

```php
<?php

class my_model extends fs_model
{
    ...
}
```

### Ejemplo Después de la Migración

```php
<?php
namespace FacturaScripts\Plugins\NOMBRE_PLUGIN\Model;

use FacturaScripts\Core\Model\Base;

class MyModel extends Base\ModelClass
{
    use Base\ModelTrait;
}
```

## Funciones Obligatorias

Los modelos ahora requieren conocer el nombre de la tabla y de la clave primaria. Debes implementar las funciones [tableName()](https://facturascripts.com/publicaciones/tablename-298) y [primaryColumn()](https://facturascripts.com/publicaciones/primarycolumnvalue-328).

```php
public static function primaryColumn()
{
    // Sustituye 'id' por el nombre de la columna que es la clave primaria
    return 'id';
}

public static function tableName()
{
    // Sustituye 'my_table' por el nombre de la tabla
    return 'my_table';
}
```

### Eliminación del Constructor

En versiones anteriores, existía un constructor extenso:

```php
public function __construct($data = false)
{
    parent::__construct('my_table');
    if ($data) {
        $this->id = $this->intval($data['id']);
        ...
    } else {
        $this->id = null;
        ...
    }
}
```

Ahora, la función [loadFromData()](https://facturascripts.com/publicaciones/loadfromdata-673) sustituye la parte 'if' y [clear()](https://facturascripts.com/publicaciones/clear-396) reemplaza el 'else'. Implementa 'clear()' si necesitas **valores por defecto:**

```php
public function clear()
{
    parent::clear();
    $this->fecha = Tools::date();
    $this->hora = Tools::hour();
}
```

## Simplificación del Código

FacturaScripts 2018 facilita un desarrollo más rápido y eficiente. Elimina funciones que ya están implementadas por defecto, tales como:
- `construct()`
- `get()` y derivados
- `exists()`
- `save()`
- `delete()`
- `test()`
- `all()` y derivados
- `url()`

Consulta más acerca de [operaciones comunes con modelos](https://facturascripts.com/publicaciones/operaciones-comunes-con-modelos-666) en nuestra documentación.

### Uso de la Clase Tools

Las funciones `no_html()` y `random_string()` ahora residen en `Core\Tools`. Añade el `use` adecuado y llama a las funciones con la sintaxis:

```php
// Coloca debajo del namespace
use FacturaScripts\Core\Tools;
...
public function test()
{
    $this->random = Tools::randomString(99);
    $this->observaciones = Tools::noHtml($this->observaciones);
}
```
