# Interacción con las Vistas

> **ID:** 690 | **Permalink:** interacturar-con-las-vistas-695 | **Última modificación:** 15-04-2025
> **URL oficial:** https://facturascripts.com/interacturar-con-las-vistas-695

Las vistas XML permiten controlar los objetos en la pantalla de manera sencilla. A continuación, se mostrarán algunos ejemplos sobre cómo acceder y modificar la configuración de una columna y su widget desde nuestro controlador. Es importante recordar dos conceptos clave:

1. **Controladores extendidos**: Son contenedores de vistas (**$this->views**).
2. **Proceso de carga**: Se realiza según el siguiente patrón:
   - **Añadir vista**: Carga la configuración de la vista para el usuario activo.
   - **Cargar datos**: Lee los datos del modelo de la vista.
   - **Pintado de la vista**: Utilizando una plantilla Twig, se compone y visualiza la vista al usuario.

Existen dos procesos donde se cargan los datos de configuración de la vista, lo que permite alterarlos antes de que el usuario los reciba.

## Selección de Columnas
Una vez cargada la vista, para modificar la configuración de una columna y/o de su widget, primero debemos acceder a la columna. Para esto, **la vista** tiene dos métodos que devuelven un objeto de la clase **ColumnItem**:
- **columnForField**: Devuelve la columna cuyo *fieldname* es igual al indicado.
- **columnForName**: Devuelve la columna que tiene como *name* el indicado.

```php
// Establece el tamaño de la columna en 6 y amplía el nivel de seguridad a 50
$column1 = $this->views['Nombre_de_Vista']->columnForField('Nombre_del_campo');
$column1->numColumns = 6;
$column1->level = 50;

// Establece una longitud máxima de los datos a 50 caracteres
$column2 = $this->views['Nombre_de_Vista']->columnForName('Nombre_de_la_columna');
$column2->widget->maxLength = 50;
```

## Activar y Desactivar Columnas
Además de poder activar y desactivar columnas mediante la propiedad *readOnly* del widget de la columna, existe un método más directo desde la vista. Para ello, utilizaremos el método *disableColumn*, indicando el *name* de la columna en el archivo XML y si deseamos que esté habilitada o no.

```php
// Desactivar mediante Widget (NO RECOMENDADO)
$column = $this->views['Nombre_de_Vista']->columnForField('Nombre_del_campo');
$column->widget->readonly = 'true';

// Desactivar mediante Vista (RECOMENDADO)
$this->views['Nombre_de_Vista']->disableColumn('Nombre_de_la_columna', true);
```

## Selección de Filtros en ListController
Para controladores que heredan de ListController, es posible personalizar o alterar los filtros añadidos a una vista. Debemos seleccionar la vista y luego seleccionar el filtro consultando la propiedad **filters**, que contiene un array con cada uno de los filtros definidos (un array de *BaseFilter*). Para seleccionar el filtro, utilizaremos el nombre que indicamos como *key* al añadirlo a la vista.

```php
// Ejemplo de carga manual de valores en filtros de tipo select
$companyFilter = $this->views['ListEmployee']->filters['company'];
$companyFilter->options['values'] = $this->codeModel->all('empresas', 'idempresa', 'nombre');

$departmentsFilter = $this->views['ListEmployee']->filters['company'];
$departmentsFilter->options['values'] = $this->codeModel->all('departments', 'id', 'name');

// Ejemplo de captura del valor del filtro
$companyFilter = $this->views['ListEmployee']->filters['company'];
if ($companyFilter->value !== '') {
    [ ... nuestras instrucciones PHP ... ]
}
```

## Añadir Botones de Acción
También puedes añadir un botón de acción utilizando el método **addButton()**. Por ejemplo, este botón redirige al controlador EditProducto.

```php
$this->addButton('ListProducto', [
    'action' => 'EditProducto',
    'icon' => 'fas fa-plus',
    'label' => 'Nuevo',
    'type' => 'link'
]);
```

Para más detalles sobre los botones, consulta la sección de [acciones en fila](https://facturascripts.com/publicaciones/row-actions-315).
