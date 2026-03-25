# Método addEditView()

> **ID:** 683 | **Permalink:** addeditview-95 | **Última modificación:** 08-05-2025
> **URL oficial:** https://facturascripts.com/addeditview-95

El método **addEditView()** permite agregar una pestaña o sección destinada a editar los datos de un único registro de un modelo en el **PanelController** o **EditController**. Este método se utiliza dentro de la función **createViews()** del controlador.

## Sintaxis
```php
$this->addEditView($viewName, $modelName, $viewTitle, $viewIcon);
```

### Parámetros
- **$viewName**: Identificador o nombre interno de la pestaña o sección. Por ejemplo: `EditProducto`.
- **$modelName**: Nombre del modelo que utilizará este listado. Por ejemplo: `Producto`.
- **$viewTitle**: Título de la pestaña o sección, que será traducido. Por ejemplo: `product`.
- **$viewIcon**: (Opcional) Icono a utilizar. Por ejemplo: `fas fa-folder`.

### Ejemplo de uso
```php
protected function createViews()
{
   $this->addEditView('EditProducto', 'Producto', 'product', 'fas fa-folder');
}
```

### XMLView
La nueva pestaña utilizará un archivo [XMLView](https://facturascripts.com/publicaciones/las-vistas-xml-xmlview-668) con el mismo nombre que la pestaña. Por ejemplo, se usará el archivo **XMLView/EditProducto.xml** para determinar qué campos se deben mostrar en el formulario.

### Cargar datos: loadData($viewName, $view)
Para cargar los valores de esta pestaña, por ejemplo al editar un producto, es necesario implementar el método **loadData()**. Este método es llamado por **PanelController** y **EditController** para cargar los valores de las pestañas o secciones.

```php
protected function loadData($viewName, $view)
{
   switch ($viewName) {
      case 'EditProducto':
         $code = $this->request->get('code');
         $view->loadData($code);
         break;
   }
}
```

El método contiene un `switch` que utiliza el parámetro **$viewName** para identificar la pestaña, permitiendo realizar acciones diferentes para cada pestaña. En este caso, se obtiene el parámetro **code** de la URL para cargar el producto correspondiente.

### Obtener valores del modelo: $this->getViewModelValue($viewName, $fieldName)
Utiliza la función **getViewModelValue()** para obtener valores del modelo de otra pestaña o vista.

```php
// Obtenemos el valor de codcliente del modelo de la pestaña EditCliente
$codcliente = $this->getViewModelValue('EditCliente', 'codcliente');
```

## 🔒 Establecer como solo lectura
Es posible configurar la vista como solo lectura. Esto cambiará la plantilla Twig utilizada para renderizar la vista, de modo que no se incluirán los botones de borrado y guardado de datos, mostrando así los datos sin posibilidad de edición. Para activar o desactivar esta opción, se debe llamar al método **setReadOnly()** de la vista.

En este ejemplo, añadimos una pestaña y la configuramos como solo lectura, de modo que todos los campos sean de solo lectura.

```php
protected function createViews()
{
   $this->addEditView('EditInfoProject', 'WebProject', 'project', 'fas fa-info')
		->setReadOnly(true);
}
```

En este otro ejemplo, modificamos una pestaña existente y la marcamos como solo lectura:

```php
$this->tab('MiPestaña')->setReadOnly(true);
```
