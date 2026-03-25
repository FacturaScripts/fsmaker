# Método primaryColumn() del modelo

> **ID:** 639 | **Permalink:** primarycolumn-492 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/primarycolumn-492

El método **primaryColumn()** del modelo devuelve el nombre de la columna que actúa como clave primaria en la tabla. Esta función, junto con [el método tableName()](https://facturascripts.com/publicaciones/tablename-298), es fundamental para la correcta implementación de un modelo en FacturaScripts.

```php
public static function primaryColumn(): string
{
    // Reemplace 'id' con el nombre de la columna que es la clave primaria
    return 'id';
}
```

Es importante recordar que además debe existir un archivo XML con el mismo nombre que la tabla en la carpeta `Table` del plugin. Este archivo contiene [la definición de la estructura de la tabla](https://facturascripts.com/publicaciones/la-definicion-de-la-estructura-de-la-tabla-514).
