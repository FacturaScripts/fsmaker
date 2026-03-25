# addEditListView()

> **ID:** 684 | **Permalink:** addeditlistview-505 | **Última modificación:** 05-06-2025
> **URL oficial:** https://facturascripts.com/addeditlistview-505

La función `addEditListView()` permite añadir una pestaña o sección en el [EditController](/publicaciones/editcontroller-642) o PanelController para editar múltiples registros de un modelo. Se utiliza dentro del método **createViews()** del controlador.

## Sintaxis

```php
$this->addEditListView($viewName, $modelName, $viewTitle, $viewIcon);
```

- **$viewName**: Identificador o nombre interno de la pestaña o sección. Ejemplo: `EditProducto`.
- **$modelName**: Nombre del modelo asociado. Ejemplo: `Producto`.
- **$viewTitle**: Título visible de la pestaña o sección (será traducido automáticamente). Ejemplo: `products`.
- **$viewIcon**: *(opcional)* Icono a utilizar. Ejemplo: `fas fa-folder`.

## Ejemplo de uso

```php
protected function createViews()
{
    // Importante: llamar primero al método del padre
    parent::createViews();

    // Añadimos la pestaña agrupando la lógica
    $this->createViewsProductos();
}

protected function createViewsProductos(string $viewName = 'EditCuentaBancoCliente')
{
    $this->addEditListView($viewName, 'CuentaBancoCliente', 'customer-banking-accounts', 'fas fa-piggy-bank');
}
```

![Ejemplo de addEditListView](https://i.imgur.com/vflxqSm.png)

---

## Versión mini o "inline"

Para activar la versión reducida o "inline" de la pestaña, añade:

```php
$this->tab($viewName)->setInLine(true);
```

---

## Relación con XMLView

La pestaña añadida utilizará un archivo [XMLView](/publicaciones/las-vistas-xml-xmlview-668) con el mismo nombre que la pestaña. Por ejemplo, para `EditCuentaBancoCliente`, el archivo debe llamarse **XMLView/EditCuentaBancoCliente.xml**. Este archivo define los campos que se mostrarán en el formulario.

---

## Método loadData($viewName, $view)

Para cargar los datos en la pestaña (por ejemplo, editar una cuenta bancaria), implementa el método `loadData()`. Este método es llamado por `PanelController` y `EditController` al cargar los valores de cada pestaña o sección.

### Ejemplo de implementación:

```php
protected function loadData($viewName, $view)
{
    switch ($viewName) {
        default:
            // Importante: mantener la llamada al padre
            parent::loadData($viewName, $view);
            break;

        case 'EditProducto':
            // Obtener codcliente desde la pestaña EditCliente y filtrar
            $codcliente = $this->getViewModelValue('EditCliente', 'codcliente');
            $where = [new DataBaseWhere('codcliente', $codcliente)];
            $view->loadData('', $where);
            break;
    }
}
```

---

## Obtener valores de otro modelo con getViewModelValue()

Utiliza `getViewModelValue()` para acceder a valores del modelo asociado a otra pestaña o vista dentro del mismo controlador.

```php
// Obtener el valor de codcliente del modelo de la pestaña EditCliente
$codcliente = $this->getViewModelValue('EditCliente', 'codcliente');
```
