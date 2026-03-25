# Clase DataBaseWhere (obsoleta)

> **ID:** 668 | **Permalink:** databasewhere-478 | **Última modificación:** 06-12-2025
> **URL oficial:** https://facturascripts.com/databasewhere-478

La clase `DataBaseWhere` en FacturaScripts se utiliza para **filtrar resultados** en los métodos [all()](https://facturascripts.com/publicaciones/all-863), [count()](https://facturascripts.com/publicaciones/count-882) y [loadFromCode()](https://facturascripts.com/publicaciones/loadfromcode-677) de los modelos. Podemos pasar un array de `DataBaseWhere` a estos métodos para aplicar los filtros deseados.

Esta clase fué reemplazada por Where a partir de la versión 2025.

## Introducción a la Clase
Para utilizar `DataBaseWhere`, primero debemos asegurarnos de cargar la clase:

```php
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
```

### Constructor de DataBaseWhere
- **fields**: Nombre del campo sobre el que se realiza el filtro. Puede ser una lista, como `'campo1|campo2|campo3'`.
- **value**: Valor por el cual se filtra. También puede compararse con otro campo usando el prefijo `field:`.
- **operator**: (opcional) `'='` por defecto. Operadores permitidos: `=`, `<`, `>`, `<=`, `>=`, `!=`, `IN`, `NOT IN`, `IS`, `IS NOT`, `LIKE`, `XLIKE`, `REGEXP`.
- **operation**: (opcional) `'AND'` por defecto. Operadores lógicos: `AND`, `OR`. Importante: esta operación se aplica al propio elemento, no al siguiente.
- **useField**: (opcional) `false` por defecto. Activa/desactiva el modificador field:xxx.

### Ejemplos de Uso

#### Ejemplo 1: Obtener productos de la familia 1234
```php
$productoModel = new Producto();
$where = [new DataBaseWhere('codfamilia', '1234')];
$productos1234 = $productoModel->all($where);
```
Este código devuelve todos los productos que pertenecen a la familia 1234.

#### Ejemplo 2: Stock por debajo del mínimo
```php
$stockModel = new Stock();
$where = [new DataBaseWhere('cantidad', 1, '<')];
$stockMinimo = $stockModel->all($where);
```

También podemos comparar contra otro campo usando el modificador `fiedl:xxx`, asignando true como 5º parámetro para activar el uso de field:

```php
$stockModel = new Stock();
$where = [new DataBaseWhere('cantidad', 'field:stockmin', '<', 'AND', true)];
$stockMinimo = $stockModel->all($where);
```

#### Ejemplo 3: Facturas de un cliente en diciembre de 2020
```php
$facturaModel = new FacturaCliente();
$where = [
   new DataBaseWhere('codcliente', '1'),
   new DataBaseWhere('fecha', '01-12-2020', '>=') ,
   new DataBaseWhere('fecha', '31-12-2020', '<=')
];
$facturasDiciembre = $facturaModel->all($where);
```

#### Ejemplo 4: Contar facturas sin pagar de un cliente
```php
$facturaModel = new FacturaCliente();
$where = [
   new DataBaseWhere('codcliente', '1'),
   new DataBaseWhere('pagada', false)
];
$numero = $facturaModel->count($where);
```

#### Ejemplo 5: Variantes con referencia o código de barras 666
```php
$varianteModel = new Variante();
$where = [new DataBaseWhere('codbarras|referencia', '666')];
$variantes666 = $varianteModel->all($where);
```

#### Ejemplo 6: Borrar clientes sin teléfono o con email admin@admin.com
```php
$clienteModel = new Cliente();
$where = [
   new DataBaseWhere('telefono1', ''),
   new DataBaseWhere('email', 'admin@admin.com', '=', 'OR')
];
foreach($clienteModel->all($where) as $cliente) {
   $cliente->delete();
}
```
El operador `OR` se aplica aquí al segundo elemento.

### 🧮 Uso de Operadores
#### Operador IS e IS NOT
```php
$where = [new DataBaseWhere('nombre', null)];
// RESULTADO: where nombre IS NULL

$where = [new DataBaseWhere('nombre', null, 'IS')];
// RESULTADO: where nombre IS NULL

$where = [new DataBaseWhere('nombre', null, 'IS NOT')];
// RESULTADO: where nombre IS NOT NULL
```

#### Operador IN y NOT IN
```php
$where = [
   new DataBaseWhere('codejercicio', '2018'),
   new DataBaseWhere('codcuentaesp', 'IVAREX,IVAREP,IVARRE', 'IN')
];
// RESULTADO: where codejercicio = '2018' and codcuentaesp in ('IVAREX','IVAREP','IVARRE')

$where = [new DataBaseWhere('codcliente', "select codcliente from contactos where codpais = 'ESP'", 'IN')];
// RESULTADO: where codcliente IN (select codcliente from contactos where codpais = 'ESP')
```

#### Operador LIKE
```php
$where = [new DataBaseWhere('nombre', 'sanchez', 'LIKE')];
// RESULTADO: where nombre LIKE '%sanchez%'
```
Buscar al principio o al final:
- Al principio: `'sanchez%'`
- Al final: `'%sanchez'`

#### Operador XLIKE
Este operador permite buscar múltiples palabras. Por ejemplo:
```php
$where = [new DataBaseWhere('descripcion', 'gran caja', 'XLIKE')];
// RESULTADO: where (descripcion LIKE '%gran%' AND descripcion LIKE '%caja%')
```
