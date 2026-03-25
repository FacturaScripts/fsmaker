# Where

> **ID:** 1632 | **Permalink:** where | **Última modificación:** 07-12-2025
> **URL oficial:** https://facturascripts.com/where

La clase **Where** se usa en FacturaScripts para definir filtros para consultar a la base de datos. En lugar de escribir el SQL directamente, podemos usar esta clase:

```
use FacturaScripts\Core\Where;

$where = [
	Where::gt('precio', 0),
	Where::isNull('codfamilia')
];

$sql = 'SELECT * FROM productos WHERE ' . Where::multiSql($where);
// SELECT * FROM productos WHERE precio > 0 AND codfamilia IS NULL
```

Esta clase se usa activamente con la clase [DbQuery](/publicaciones/dbquery) y pronto con los nuevos modelos:

```
use FacturaScripts\Core\DbQuery;
use FacturaScripts\Core\Where;

$data = DbQuery::table('productos')
	->whereMulti([
		Where::gt('precio', 0),
		Where::isNull('codfamilia')
	])->get();
```

## 🧮 Operadores
Existe una función para cualquier operador que queramos usar en el where, de forma que el código sea lo más legible posible:

- ``eq`` (=)
- ``NotEq`` (<>)
- ``lt`` (<)
- ``lte`` (<=)
- ``gt`` (>)
- ``gte`` (>=)
- ``isNull`` (IS NULL)
- ``isNotNull`` (IS NOT NULL)
- ``in`` (IN)
- ``notIn`` (NOT IN)
- ``between`` (BETWEEN)
- ``notBetween`` (NOT BETWEEN)
- ``like`` (LIKE)
- ``notLike`` (NOT LIKE)
- ``xlike`` : esta es una versión nuestra del operador like donde previamente desglosamos las palabras a buscar. Así si busas 'mi casa' mostrará tanto las coincidencias con 'mi casa' como las que coincidan con 'casa mi'.

```
$where = [
	Where::eq('codfabricante, '123'),
	Where::isNull('codfamilia'),
	Where::lt('precio', 10)
];
// WHERE codfabricante = '123' AND codfamilia IS NULL AND precio < '10'
```

### 👀 Debug
Puedes ver el sql que genera llamando al método `multiSql()`

```
$where = [
	Where::eq('codfabricante, '123'),
	Where::isNull('codfamilia'),
	Where::lt('precio', 10)
];
echo Where::multiSql($where);
```

### Uso de paréntesis
El siguiente where generaría este SQL: ``sevende = true AND ventasinstock = false OR secompra = true AND nostock = false``

```
$where = [
	Where::eq('sevende', true),
	Where::eq('ventasinstock', false),
	Where::orEq('secompra', true),
	Where::eq('nostock', false)
];
```

Pero quizás queríamos agrupar el segundo y tercer elemento o el tercero y el cuarto. Para evitar sorpresas necesitamos una forma intuitiva de indicar cuando agrupar y qué elementos agrupar. Vemos cada una de las posibles combinaciones:

#### ( and ) or ( and )
```
$where = [
	Where::sub([
		Where::eq('sevende', true),
		Where::eq('ventasinstock', false)
	]),
	Where::orSub([
		Where::eq('secompra', true),
		Where::eq('nostock', false),
	]),
];
```

#### and ( or ) and
```
$where = [
	Where::eq('sevende', true),
	Where::sub([
		Where::eq('ventasinstock', false),
		Where::orEq('secompra', true),
	]),
	Where::eq('nostock', false)
];
```

### 🟰 Comparar campos con field:xxx
Podemos comparar campos con otros campos de la tabla usando el modificador `field:xxx`, que debe habilitarse con el método `useField()` para evitar problemas:

```
$where = [Where::lt('cantidad, 'field:stockmin')->useField()];
// WHERE cantidad < stockmin
```
