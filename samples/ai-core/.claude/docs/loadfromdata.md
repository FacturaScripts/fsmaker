# $modelo-&gt;loadFromData()

> **ID:** 636 | **Permalink:** loadfromdata-673 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/loadfromdata-673

El método `loadFromData()` de un modelo asigna los valores de un array proporcionado al objeto.

### Parámetros
- **Primer parámetro (requerido):** Un array clave/valor que contiene el nombre de la columna y su correspondiente valor a asignar.
- **Segundo parámetro (opcional):** Un array que incluye los nombres de las columnas que se desean excluir del primer parámetro.

### Ejemplo de uso
```php
$familia = new Familia();
var_dump($familia->codfamilia); // Devuelve NULL

$familia->loadFromData(['codfamilia' => '1234', 'descripcion' => 'Descripción 1234']);
var_dump($familia->codfamilia); // Devuelve 1234
```

### Ejemplo de exclusión de columnas
```php
$familia = new Familia();
$familia->codfamilia = '123';
$familia->descripcion = 'Familia 123';

$familia->loadFromData(['codfamilia' => '666', 'descripcion' => 'Familia 666'], ['descripcion']);
var_dump($familia->codfamilia); // Devuelve 666
var_dump($familia->descripcion); // Devuelve Familia 123
```
