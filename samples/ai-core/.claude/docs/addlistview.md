# addListView()

> **ID:** 685 | **Permalink:** addlistview-259 | **Última modificación:** 07-11-2025
> **URL oficial:** https://facturascripts.com/addlistview-259

Añade una pestaña o sección con un **listado** al [EditController](/publicaciones/editcontroller-642) o PanelController. Esta función se utiliza dentro de la función **createViews()** del controlador.

## Método: `$this->addListView($viewName, $modelName, $viewTitle, $viewIcon)`
- **$viewName**: Identificador o nombre interno de la pestaña o sección. Por ejemplo: `ListProducto`.
- **$modelName**: Nombre del modelo que utilizará este listado. Por ejemplo: `Producto`.
- **$viewTitle**: Título de la pestaña o sección. Este título será traducido. Por ejemplo: `products`.
- **$viewIcon**: (opcional) Icono a utilizar. Por ejemplo: `fas fa-search`.

### Ejemplo de uso:

```php
protected function createViews() {
   // Debemos dejar esta llamada a parent del EditController.
   parent::createViews();

   // Añadimos una pestaña con un listado de productos
   $this->addListView('ListProducto', 'Producto', 'products');
}
```

En este ejemplo, añadimos una pestaña/sección llamada `ListProducto`, que utiliza el modelo `Producto` (cada una de las líneas del listado será un objeto `Producto`), tiene el título `products` (que se traduce automáticamente) y el icono predeterminado.

![Ejemplo de listview](https://i.imgur.com/Ud1Tkin.png)

### XMLView
Esta nueva pestaña hará uso de un archivo [XMLView](/publicaciones/las-vistas-xml-xmlview-668) con el mismo nombre que la pestaña, es decir, utilizará el archivo **XMLView/ListProducto.xml** para determinar qué columnas debe mostrar en el listado.

### Método: `loadData($viewName, $view)`
Para cargar valores en esta pestaña, debemos implementar el método `loadData()`, que es la función que se llama cuando FacturaScripts necesita cargar los valores a mostrar en las pestañas.

```php
protected function loadData($viewName, $view) {
   switch ($viewName) {
      case 'ListProducto':
         // Para este ejemplo, vamos a cargar los productos sin fabricante.
         $where = [new DataBaseWhere('codfabricante', null, 'IS')];
         $view->loadData('', $where);
         break;
   }
}
```

En este caso, utilizamos un `switch` sobre el parámetro **$viewName** (el nombre de la pestaña) para personalizar la carga de datos para cada pestaña. En el caso de la pestaña `ListProducto`, hemos programado la carga de todos los productos sin fabricante.

### Método: `addColor()`
Puedes añadir colores a las filas de los listados desde el controlador. Para más información, visita "[Añadir colores desde el controlador](https://facturascripts.com/publicaciones/row-status-477)".

Desde `PanelController` o `EditController`:

```php
$this->listView($viewName)->addColor($value, $status->idestado, $color, $title);
```

La **función `addColor()`** tiene los siguientes parámetros:
* **$fieldName**: Nombre del campo donde se realizará la comprobación, correspondiente al *fieldname* del XML.
* **$value**: Valor a comprobar; el `fieldName` se verificará con este valor.
* **$color**: Color a mostrar en la fila.
* **$title**: Texto que se mostrará en la leyenda de los colores.
