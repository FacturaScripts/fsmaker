# addFilterNumber()

> **ID:** 677 | **Permalink:** addfilternumber-4 | **Última modificación:** 25-03-2025
> **URL oficial:** https://facturascripts.com/addfilternumber-4

La función **addFilterNumber()** añade un filtro de tipo **numérico** a la pestaña del **ListController**. Gracias a este método, es posible filtrar los resultados aplicando un filtro sobre el campo especificado.

## Parámetros

- **viewName**: Nombre identificador de la pestaña donde se aplicará el filtro.
- **key**: Identificador del filtro, generalmente el nombre del campo a filtrar.
- **label**: Etiqueta que se mostrará en el filtro. **Esta será traducida**.
- **field**: Campo del modelo sobre el que se aplicará el filtro.
- **operation** (opcional): Operación de comparación a aplicar. Por defecto es `>=`.

![cómo se ve el filtro](https://i.imgur.com/JRUhHn3.gif)

## Uso en ListController

### Ejemplo básico

A continuación, un ejemplo para filtrar registros en el que el total es mayor o igual que un valor especificado:

```php
// Añadir filtro: total mayor o igual
$this->addFilterNumber($viewName, 'total-gt', 'amount', 'total', '>=');
```

### Ejemplo con selección de pestaña

Se puede seleccionar inicialmente la pestaña utilizando el método `listView()`:

```php
// Seleccionar la pestaña y añadir un filtro
$this->listView($viewName)->addFilterNumber('total-gt', 'amount', 'total', '>=');
```

### Ejemplo encadenado

Es posible encadenar múltiples filtros en la misma vista:

```php
// Encadenar filtros para total mayor y total menor
$this->listView($viewName)
    ->addFilterNumber('total-gt', 'amount', 'total', '>=')
    ->addFilterNumber('total-lt', 'amount', 'total', '<');
```

## Uso en EditController

Se puede utilizar la función dentro de un EditController para agregar filtros tanto para valores mayores o iguales como para menores o iguales:

```php
// Añadir filtros en un EditController
$this->listView($viewName)
    ->addFilterNumber('total-gt', 'amount', 'total', '>=')
    ->addFilterNumber('total-lt', 'amount', 'total', '<=');
```

Con estos ejemplos se ilustra cómo utilizar la función addFilterNumber() para filtrar listados numéricos de forma sencilla y flexible dentro de FacturaScripts.
