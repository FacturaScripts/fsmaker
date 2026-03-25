# Método count() de los modelos

> **ID:** 623 | **Permalink:** count-882 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/count-882

El método `count()` de los modelos de FacturaScripts devuelve el número de registros en la tabla.

## Ejemplo
Para obtener el número de productos debemos llamar al método `count()` del modelo `Producto`:

```php
$total = Producto::count();
```

## Modelo::count(where)

Si le pasamos como parámetro un array de [Where](https://facturascripts.com/publicaciones/where) nos devolverá el número de registros que cumplen con esos filtros.

### Ejemplo: Obtener el número de productos con stock mayor que 0

Para este ejemplo le pasamos como parámetro un filtro Where::gt() sobre la columna `stockfis` del producto, que es el campo donde se guarda el stock del producto.

```php
$where = [Where::gt('stockfis', 0)];
$total = Producto::count($where);
```
