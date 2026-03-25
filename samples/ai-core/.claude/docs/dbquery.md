# DbQuery

> **ID:** 1707 | **Permalink:** dbquery | **Última modificación:** 26-01-2026
> **URL oficial:** https://facturascripts.com/dbquery

La clase **DbQuery** de FacturaScripts permite realizar una amplia variedad de consultas a la base de datos de manera sencilla.

## Seleccionar una tabla
Para hacer consultas sobre una tabla, debemos llamar al método `table()` de la clase **DbQuery**. Por ejemplo, para obtener todos los registros de la tabla de familias, podemos ejecutar:

```php
use FacturaScripts\Core\DbQuery;

$familias = DbQuery::table('familias')->get();
```

Con la función `get()` obtenemos todos los registros y columnas. Si solo queremos algunas columnas, podemos especificarlo con el método `select()`, separando los diferentes nombres de columna por comas:

```php
$descripciones_familias = DbQuery::table('familias')->select('codfamilia, descripcion')->get();
```

### Filtrar los resultados
Es posible aplicar filtros a la consulta. Por ejemplo, para obtener todos los productos de la familia `123`, podemos usar:

```php
// con whereEq aplicamos un filtro de columna = valor
$productos = DbQuery::table('productos')->whereEq('codfamilia', '123')->get();

// también podemos usar un array Where
$productos = DbQuery::table('productos')->where([Where:eq('codfamilia', '123')])->get();

// o con un filtro dinámico
$productos = DbQuery::table('productos')->whereCodfamilia('123')->get();
```

La clase soporta filtros directos como `whereBetween()`, `whereEq()`, `whereGt()`, `whereGte()`, `whereIn()`, `whereLike()`, `whereLt()`, `whereLte()`, `whereNotEq()`, `whereNotIn()`, `whereNotNull()` y `whereNull()`. También se puede utilizar un array [Where](https://facturascripts.com/publicaciones/where) para filtros más complejos.

Para mayor comodidad, se pueden aplicar filtros dinámicos usando `where` seguido del nombre de la columna:

```php
// where codfamilia = '123'
$productos = DbQuery::table('productos')->whereCodfamilia('123')->get();

// where codimpuesto = 'IVA21'
$productos = DbQuery::table('productos')->whereCodimpuesto('IVA21')->get();
```

### Ordenar los resultados
Para ordenar los resultados, se pueden utilizar las funciones `orderBy()` y `orderMulti()`:

```php
// obtenemos los productos ordenados por precio y stock
$productos = DbQuery::table('productos')
	->orderBy('precio', 'ASC')
	->orderBy('stockfis', 'ASC')
	->get();

// lo mismo, pero con orderMulti()
$productos = DbQuery::table('productos')
	->orderMulti(['precio' => 'ASC', 'stockfis' => 'ASC'])
	->get();
```

Las llamadas a `orderBy()` y `orderMulti()` añaden esos parámetros a la consulta, es decir, no reemplazan el orden previo. Si deseamos limpiar o quitar el orden anterior, debemos usar la función `reorder()`.

### Obtener el primer resultado
Para obtener solamente el primer resultado de la consulta, utilizamos el método `first()`:

```php
$primera = DbQuery::table('familias')->first();
```

### Obtener el número de registros
Usamos el método `count()` para obtener el número total de registros en la tabla:

```php
$count = DbQuery::table('familias')->count();
```

Si necesitamos el número de valores distintos de una columna, por ejemplo, saber a cuántos países distintos hemos vendido material, podemos hacerlo de varias formas:

```php
// opción 1
$numero = DbQuery::table('facturascli')->count('codpais');

// opción 2
$numero = DbQuery::table('facturascli')->selectRaw('DISTINCT codpais')->count();

// opción 3
$data = DbQuery::table('facturascli')->selectRaw('COUNT(DISTINCT codpais) as c')->first();
$numero = $data['c'];
```

### Obtener máximo, mínimo, media, suma...
Podemos realizar operaciones sobre los resultados, como obtener el valor máximo, el mínimo, la media o la suma:

```php
// el máximo precio de los productos
$max = DbQuery::table('productos')->max('precio');

// el mínimo precio de los productos
$min = DbQuery::table('productos')->min('precio');

// el precio medio de los productos
$avg = DbQuery::table('productos')->avg('precio');

// la suma de todos los precios de los productos
$sum = DbQuery::table('productos')->sum('precio');
```

Podemos aplicar filtros a los resultados antes de llamar a la función:

```php
// el máximo precio de los productos de la familia 123
$max = DbQuery::table('productos')->whereEq('codfamilia', '123')->max('precio');
```

### Insertar registros en la tabla
Para añadir registros a una tabla, utilizamos el método `insert()`:

```php
// creamos el producto 777
DbQuery::table('productos')->insert([
	'referencia' => '777',
	'descripcion' => 'Producto 777',
	'precio' => 7.77
]);
```

El método `insert()` permite insertar múltiples registros a la vez:

```php
// creamos los productos 888 y 999
DbQuery::table('productos')->insert([
	['referencia' => '888', 'descripcion' => 'Producto 888', 'precio' => 8.88],
	['referencia' => '999', 'descripcion' => 'Producto 999', 'precio' => 9.99],
]);
```

### Actualizar registros en la tabla
Podemos actualizar un registro combinando los métodos `whereEq()` y `update()`:

```php
// actualizamos el precio y stock del producto 777
DbQuery::table('productos')
	->whereEq('referencia', '777')
	->update([
		'precio' => 8,
		'stockfis' => 11,
	]);
```

Si no aplicamos ningún filtro `where`, los cambios se aplicarán a toda la tabla. Por ejemplo, para marcar todos los productos como públicos:

```php
// marcamos todos los productos como públicos
DbQuery::table('productos')
	->update([
		'publico' => true,
	]);
```

### Eliminar registros de la tabla
Al combinar los métodos `whereEq()` y `delete()`, podemos eliminar un registro específico:

```php
// eliminamos el producto 777
DbQuery::table('productos')
	->whereEq('referencia', '777')
	->delete();
```

También podemos eliminar todos los registros de una tabla omitiendo el filtro `where`:

```php
// eliminamos todos los productos
DbQuery::table('productos')->delete();
```
