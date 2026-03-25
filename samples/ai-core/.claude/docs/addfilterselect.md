# addFilterSelect()

> **ID:** 678 | **Permalink:** addfilterselect-572 | **Última modificación:** 10-07-2024
> **URL oficial:** https://facturascripts.com/addfilterselect-572

Añade un filtro de tipo **selector** a la pestaña del **ListController**. Permite filtrar los resultados por el campo indicado.

## Parámetros:
- **viewName**: nombre identificador de la pestaña.
- **key**: identificador del filtro. Generalmente el nombre del campo que quieras filtrar.
- **label**: etiqueta a mostrar en el filtro. **Se traducirá**.
- **field**: campo del modelo sobre el que aplicar el filtro.
- **values**: array de valores posibles para filtrar.

![addFilterSelect()](https://i.imgur.com/rhAFnDv.gif)

### Ejemplo: filtrar facturas por país.
```
$countries = $this->codeModel->all('paises', 'codpais', 'nombre');
$this->addFilterSelect('ListFacturaCliente', 'codpais', 'country', 'codpais', $countries);
```

### Ejemplo: filtrar facturas por ciudad.
```
$cities = $this->codeModel->all('facturascli', 'ciudad', 'ciudad');
$this->addFilterSelect('ListFacturaCliente', 'ciudad', 'city', 'ciudad', $cities);
```

### Ejemplo: filtrar por valore fijos
```
$countries = [
	['code' => 'ESP', 'description' => 'Spain'],
	['code' => 'USA', 'description' => 'United States'],
];
$this->addFilterSelect('ListFacturaCliente', 'codpais', 'country', 'codpais', $countries);

// también funciona así
$countries = [
	'ESP' => 'Spain',
	'USA' => 'United States',
];
$this->addFilterSelect('ListFacturaCliente', 'codpais', 'country', 'codpais', $countries);
```

### Ejemplo en un EditController
```
$countries = $this->codeModel->all('paises', 'codpais', 'nombre');
$this->listView($viewName)->addFilterSelect('codpais', 'country', 'codpais', $countries);
```
