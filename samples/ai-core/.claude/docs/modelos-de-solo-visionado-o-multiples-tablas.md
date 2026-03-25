# Modelos de Más de Una Tabla

> **ID:** 669 | **Permalink:** modelos-de-solo-visionado-o-multiples-tablas-777 | **Última modificación:** 03-08-2025
> **URL oficial:** https://facturascripts.com/modelos-de-solo-visionado-o-multiples-tablas-777

En ocasiones, es necesario mostrar listados que consulten **más de una tabla**. Si el [widget select](https://facturascripts.com/publicaciones/widget-select-557) o el [widget autocomplete](https://facturascripts.com/publicaciones/widget-autocomplete-946) no resuelven nuestro problema, podemos utilizar **JoinModel** para solucionarlo.

## ¿Qué es un JoinModel?
El JoinModel es un tipo especial de modelo que **se utiliza exclusivamente para listados**. Esto significa que **no está diseñado** para crear, editar o eliminar datos. Su única función es la visualización de datos.

### Ejemplo de Uso de JoinModel
Para crear un JoinModel, debemos definir una clase en la carpeta **Model/Join** de nuestro plugin que herede de **Core/Model/Base/JoinModel**. Esta clase debe implementar los métodos `getTables()`, `getFields()` y `getSQLFrom()`, los cuales especificarán las tablas y campos a utilizar.

**Ejemplo: Model/Join/PartidaAsiento.php**: A continuación, crearemos un JoinModel que combine las tablas `partidas` y `asientos`.

```php
namespace FacturaScripts\Plugins\MyNewPlugin\Model\Join;

use FacturaScripts\Core\Model\Base\JoinModel;

class PartidaAsiento extends JoinModel
{
    protected function getFields(): array
    {
        return [
            'concepto' => 'partidas.concepto',
            'debe' => 'partidas.debe',
            'fecha' => 'asientos.fecha',
            'haber' => 'partidas.haber',
            'idasiento' => 'partidas.idasiento',
            'idpartida' => 'partidas.idpartida',
            'numero' => 'asientos.numero'
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'partidas LEFT JOIN asientos ON partidas.idasiento = asientos.idasiento';
    }

    protected function getTables(): array
    {
        return ['asientos', 'partidas'];
    }
}
```

### Descripción de los Métodos

#### `getTables()`
Este método debe devolver el listado de tablas que vamos a utilizar:
```php
protected function getTables(): array
{
    return [
        'asientos',
        'partidas',
        'subcuentas'
    ];
}
```

#### `getFields()`
Este método debe devolver un array asociativo con los campos que vamos a utilizar y a qué tabla corresponden:
```php
protected function getFields(): array
{
    return [
        'codejercicio' => 'asientos.codejercicio',
        'codcuentaesp' => 'subcuentas.codcuentaesp',
        'descripcion' => 'cuentasesp.descripcion',
        'codimpuesto' => 'subcuentas.codimpuesto',
        'iva' => 'partidas.iva',
        'recargo' => 'partidas.recargo',
        'baseimponible' => 'SUM(partidas.baseimponible)'
    ];
}
```

#### `getSQLFrom()`
Este método debe devolver la consulta SQL con los JOIN correspondientes:
```php
protected function getSQLFrom(): string
{
    return 'asientos'
        . ' INNER JOIN partidas ON partidas.idasiento = asientos.idasiento'
        . ' INNER JOIN subcuentas ON subcuentas.idsubcuenta = partidas.idsubcuenta'
        . ' AND subcuentas.codimpuesto IS NOT NULL'
        . ' AND subcuentas.codcuentaesp IS NOT NULL'
        . ' LEFT JOIN cuentasesp ON cuentasesp.codcuentaesp = subcuentas.codcuentaesp';
}
```

## Métodos Opcionales

### `getGroupFields()`
En casos donde se desee agrupar información para obtener totales o datos estadísticos, podemos definir las cláusulas *GROUP BY* y *HAVING* de la sentencia SQL mediante este método. Debe devolver una cadena de texto con los valores a aplicar:
```php
protected function getGroupFields(): string
{
    return 'asientos.codejercicio, subcuentas.codcuentaesp,'
        . 'cuentasesp.descripcion, subcuentas.codimpuesto,'
        . 'partidas.iva, partidas.recargo';
}
```

### `primaryColumnValue()`
Este método permite especificar cuál es la clave primaria. Al cargar el JoinModel, los checkboxes de cada fila tendrán como valor la clave primaria correspondiente. Este método es necesario si se requiere añadir alguna función extra, como un botón personalizado que interactúe con los checkboxes seleccionados:
```php
public function primaryColumnValue()
{
    return $this->codejercicio;
}
```

## Edición y Borrado de Datos
Este modelo no permite la edición ni el borrado directamente, ya que utiliza distintas tablas en el proceso de visualización de datos. Sin embargo, se puede establecer un modelo como *principal* sobre el cual se realizarán los procesos de edición y borrado.

Para establecer el modelo principal, se debe llamar al método **setMasterModel()** desde el constructor, pasándole una instancia del modelo:
```php
public function __construct($data = [])
{
    parent::__construct($data);
    $this->setMasterModel(new Cliente());
}
```

### Métodos `clear()` y `loadFromData()`
Si necesitamos calcular algunos valores, podemos utilizar los métodos `clear()` o `loadFromData()` para ello:
```php
public function clear()
{
    parent::clear();
    $this->cuotaiva = 0.00;
    $this->cuotarecargo = 0.00;
    $this->total = 0.00;
}

protected function loadFromData($data)
{
    parent::loadFromData($data);
    $this->cuotaiva = $this->baseimponible * ($this->iva / 100.00);
    $this->cuotarecargo = $this->baseimponible * ($this->recargo / 100.00);
    $this->total = $this->baseimponible + $this->cuotaiva + $this->cuotarecargo;
}
```
