# addFilterDatePicker()

> **ID:** 676 | **Permalink:** addfilterdatepicker-946 | **Última modificación:** 10-11-2025
> **URL oficial:** https://facturascripts.com/addfilterdatepicker-946

Añade un **filtro** de tipo **fecha** (solo fecha, sin hora) a la pestaña del **ListController**. Este filtro permite filtrar los resultados según la columna indicada.

## Parámetros:
- **viewName**: Nombre identificador de la pestaña.
- **key**: Nombre identificador del filtro. Generalmente, es el nombre del campo que deseas filtrar.
- **label**: Etiqueta que se mostrará en el filtro. **Se traducirá** automáticamente.
- **field**: Nombre del campo del modelo donde se aplica el filtro.
- **operation**: La operación a aplicar (`>`, `>=`, `=`, `<`, `<=`). Por defecto es `>=`.
- **dateTime**: Indica si el campo es de tipo datetime (`true` para incluir fecha y hora).

![addFilterDatePicker](https://i.imgur.com/cDqU9yH.gif)

### Ejemplo en un ListController
```php
// Añade un filtro con operación >=
$this->addFilterDatePicker($viewName, 'fecha', 'date', 'fecha');

// Si queremos que la operación sea <=
// $this->addFilterDatePicker($viewName, 'fecha', 'date', 'fecha', '<=');

// Si el campo es de tipo datetime o timesince, se haría así
// $this->addFilterDatePicker($viewName, 'fecha', 'date', 'fecha', '<=', true);
```

### Ejemplo en un EditController
```php
$this->listView($viewName)->addFilterDatePicker('fecha', 'date', 'fecha');

// Si queremos que la operación sea <=
// $this->listView($viewName)->addFilterDatePicker('fecha', 'date', 'fecha', '<=');

// Si el campo es de tipo datetime o timesince, se haría así
// $this->listView($viewName)->addFilterDatePicker('fecha', 'date', 'fecha', '<=', true);
```
