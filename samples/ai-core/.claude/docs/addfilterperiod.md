# addFilterPeriod()

> **ID:** 680 | **Permalink:** addfilterperiod-315 | **Última modificación:** 04-02-2026
> **URL oficial:** https://facturascripts.com/addfilterperiod-315

La función `addFilterPeriod()` permite añadir un filtro de rango de fechas en la pestaña del **ListController**. Esto facilita filtrar los datos mostrados en dicha pestaña según un rango de fechas específico.

### Controles Añadidos
El filtro añade tres controles a la vista:
- Un **select** que permite elegir entre periodos predefinidos (como 'este mes', 'este trimestre', etc.).
- Dos campos de tipo **date** para definir la fecha de inicio ('desde') y la fecha de fin ('hasta') para el filtro.

### Parámetros
Los parámetros de la función son los siguientes:
- **viewName**: Nombre identificador de la pestaña donde se aplicará el filtro.
- **key**: Identificador del filtro. Generalmente, este es el nombre del campo que deseas filtrar.
- **label**: Etiqueta que se mostrará en el filtro; **se traducirá** automáticamente si es necesario.
- **field**: Campo del modelo sobre el cual se aplicará el filtro.
- **dateTime**: Valor booleano (`true` o `false`) que indica si el campo es de tipo datetime (incluye fecha y hora).

![addFilterPeriod()](https://i.imgur.com/4CzcWEp.gif)

### Ejemplo en un ListController
```php
$this->addFilterPeriod($viewName, 'date', 'period', 'fecha');

// Si el campo es de tipo datetime o timesince, utilizaríamos de la siguiente forma:
// $this->addFilterPeriod($viewName, 'date', 'period', 'fecha', true);
```

### Ejemplo en un EditController
```php
$this->listView($viewName)->addFilterPeriod('date', 'period', 'fecha');

// Si el campo es de tipo datetime o timesince, utilizaríamos de la siguiente forma:
// $this->listView($viewName)->addFilterPeriod('date', 'period', 'fecha', true);
```
