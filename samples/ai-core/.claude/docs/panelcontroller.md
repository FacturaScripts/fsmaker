# PanelController

> **ID:** 682 | **Permalink:** panelcontroller-845 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/panelcontroller-845

El PanelController, al igual que el [ListController](https://facturascripts.com/publicaciones/listcontroller-232), es un **controlador extendido** que permite múltiples vistas o pestañas. En este caso, admite distintos tipos de vistas:

- **[ListView](https://facturascripts.com/publicaciones/addlistview-259)**: Para mostrar listados.
- **[EditView](https://facturascripts.com/publicaciones/addeditview-95)**: Para editar los datos de un único modelo.
- **[EditListView](https://facturascripts.com/publicaciones/addeditlistview-505)**: Para editar múltiples registros de un modelo.
- **[HTMLView](https://facturascripts.com/publicaciones/addhtmlview-794)**: Para mostrar HTML con libertad total.

El controlador divide la pantalla en dos zonas: la izquierda es la **zona de navegación** y la derecha visualiza las vistas con los datos correspondientes.

Para usar este controlador, es necesario crear [vistas en formato XML](https://facturascripts.com/publicaciones/las-vistas-xml-xmlview-668), de manera similar a otros controladores extendidos.

## Ejemplo: EditFabricante.php
```php
<?php
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\PanelController;

class EditFabricante extends PanelController
{
    public function getPageData(): array
    {
        $page = parent::getPageData();
        $page['title'] = 'manufacturer';
        $page['menu'] = 'warehouse';
        $page['icon'] = 'fa-folder-open';
        $page['showonmenu'] = false;
        return $page;
    }

    protected function createViews() {
        $this->addEditView('EditFabricante', 'Fabricante', 'manufacturer');
        $this->addListView('ListProducto', 'Producto', 'products');
    }

    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'EditFabricante':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'ListProducto':
                $where = [new DataBaseWhere('codfabricante', $this->getModel()->primaryColumnValue())];
                $view->loadData('', $where);
                break;
        }
    }
}
```

### Método createViews()

Dentro de este método, en nuestra nueva clase, debemos ir creando las diferentes vistas o pestañas que se visualizarán, empleando distintos métodos según el tipo de vista que estemos añadiendo. Al añadir una vista, es necesario especificar el modelo (nombre completo) y el nombre de la vista XML, y opcionalmente el título y el icono para el grupo de navegación:

- **[addEditView()](https://facturascripts.com/publicaciones/addeditview-95)**: Añade una pestaña o vista para editar datos de un único registro de un modelo.
- **[addEditListView()](https://facturascripts.com/publicaciones/addeditlistview-505)**: Añade una vista o pestaña para editar múltiples registros de un modelo.
- **[addListView()](https://facturascripts.com/publicaciones/addlistview-259)**: Añade una pestaña o vista para visualizar en modo lista múltiples registros de un modelo.
- **[addHtmlView()](https://facturascripts.com/publicaciones/addhtmlview-794)**: Añade una pestaña o vista con total libertad sobre el HTML.

Es posible añadir varias vistas o pestañas para un mismo modelo usando una vista XML. Para ello, al añadir la vista se debe emplear un índice numérico comenzando desde 1 y separando el nombre de la vista del índice con un guión ('-').

```php
$this->addListView('ListPartidaImpuesto-1', 'PartidaImpuesto', 'purchases', 'fas fa-sign-in-alt');
$this->addListView('ListPartidaImpuesto-2', 'PartidaImpuesto', 'sales', 'fas fa-sign-out-alt');
```

Este método tiene una visibilidad de *protected*, lo que permite a los plugins extender nuestra clase y añadir nuevas vistas o modificar las existentes.

### Método loadData()

Este método es llamado por cada una de las vistas para cargar los datos específicos. La llamada incluye el identificador de la vista y el objeto view, permitiendo acceder a todas sus propiedades. La carga de datos puede variar según el tipo de vista, por lo que es responsabilidad del programador asegurar que se carguen correctamente. Aunque esto puede parecer un desafío, también brinda un mayor control sobre los datos que se leen del modelo.

#### ⚠️ Propiedad $this->hasData

Este controlador verifica durante la carga (en loadData) si **el modelo de la primera pestaña** existe en la base de datos. Por ejemplo, si hemos añadido una pestaña EditView con el modelo Producto en primera posición, comprobará si el producto existe. De no ser así, la navegación entre pestañas quedará bloqueada.

![Pestañas bloqueadas](https://i.imgur.com/huZ4gVO.png)

Si deseamos que las pestañas siempre estén activas, podemos establecer $this->hasData a true en loadData(). Por ejemplo:

```php
$this->hasData = true;
```

### Método setTabsPosition()

Este método permite colocar las pestañas a la izquierda, arriba, abajo o abajo a la izquierda:

```php
$this->setTabsPosition('left');       // coloca las pestañas a la izquierda
$this->setTabsPosition('top');        // coloca las pestañas arriba
$this->setTabsPosition('bottom');     // coloca las pestañas abajo (la primera pestaña queda arriba y las demás debajo)
$this->setTabsPosition('left-bottom'); // coloca las pestañas abajo a la izquierda (la primera queda arriba y las demás abajo)
```

Cuando están colocadas abajo (bottom), se muestra la ventana principal (primera vista que se añade) y debajo la información de la pestaña seleccionada. Si solo hay una vista o pestaña (además de la principal), se muestra directamente sin el diseño de pestañas.
