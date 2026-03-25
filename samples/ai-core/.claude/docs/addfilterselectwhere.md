# addFilterSelectWhere()

> **ID:** 679 | **Permalink:** addfilterselectwhere-790 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/addfilterselectwhere-790

Añade un filtro de tipo **select** a la pestaña del **ListController**. Este filtro tiene la característica de ser **un filtro de filtros configurables**. Cada una de las opciones del select aplica un filtro [Where](/publicaciones/where) predefinido en el controlador. Por ejemplo, una opción puede filtrar todos los datos que tengan `false` en la columna `debaja`, y otra opción puede filtrar todos los registros cuyo `codpais` sea `ESP`. Además, si el usuario no selecciona ninguna opción, **se aplica automáticamente la primera opción**.

## Parámetros:
- **viewName**: Nombre identificador de la pestaña.
- **key**: Identificador del filtro. Generalmente, se utiliza el nombre del campo que se desea filtrar.
- **label**: Etiqueta que se mostrará en el filtro. **Se traducirá** automáticamente.
- **values**: Array que contiene la lista de valores a mostrar en el desplegable. Se compone de una etiqueta y un array de [Where](/publicaciones/where), el cual se aplicará al seleccionar esa opción.

![addFilterSelectWhere()](https://i.imgur.com/QGa06pI.gif)

### Ejemplo: filtrar los clientes según su estado
Filtrar en la lista de clientes (vista `ListCliente`) los clientes según el estado designado en su ficha.

```
$this->addFilterSelectWhere('ListCliente', 'status', [
	['label' => Tools::trans('all'), 'where' => []],
	['label' => Tools::trans('only-active'), 'where' => [Where::eq('debaja', false)]],
	['label' => Tools::trans('only-suspended'), 'where' => [Where::eq('debaja', true)]]
]);
```

### Ejemplo en un EditController
En los `EditController`, no se puede llamar directamente a `$this->addFilterSelectWhere()`. Debe hacerse a través de `$this->views`.

```
$this->views[$viewName]->addFilterSelectWhere('status', [
	['label' => Tools::trans('all'), 'where' => []],
	['label' => Tools::trans('only-active'), 'where' => [Where::eq('debaja', false)]],
	['label' => Tools::trans('only-suspended'), 'where' => [Where::eq('debaja', true)]]
]);
```
