# Extensiones de controladores

> **ID:** 919 | **Permalink:** extensiones-de-controladores | **Última modificación:** 19-03-2026
> **URL oficial:** https://facturascripts.com/extensiones-de-controladores

Para modificar el comportamiento o añadir pestañas o secciones a controladores de otros plugins (o del core) podemos usar una extensión o pipe, es decir, crearemos un archivo php con el mismo nombre que el controlador en la carpeta **Extension/Controller** de nuestro plugin.

## Las extensiones no son herencia
Las extensiones no son herencia. No se puede extender cualquier función imaginable, solamente las que tienen soporte. Y cada función que añadas en una extensión debe tener un **return function()**.

### Métodos disponibles para extender
- **createViews()** se ejecuta una vez realizado el [createViews() del controlador](/publicaciones/listcontroller-232).
- **execPreviousAction()** se ejecuta después del [execPreviousAction() del controlador](/publicaciones/controladores-extendidos-367). **Si devolvemos false** detenemos la ejecución del controlador.
- **loadData()** se ejecuta tras el loadData() del controlador. Recibe los parámetros $viewName y $view.
- **execAfterAction()** se ejecuta tras el [execAfterAction() del controlador](/publicaciones/controladores-extendidos-367).
- **selectAction** se ejecuta antes de cargar datos en el [widget select](/publicaciones/widget-select-557).

### Ejemplo createViews
Esto sirve para poder añadir pestañas o cualquier método a controladores que ya existen. Siguiendo con el ejemplo vamos a añadir la pestaña de logs al listado de productos (controlador ListProducto). Para ello creamos el archivo **Extension/Controller/ListProducto.php**:

```
<?php
namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
   public function createViews(): Closure
   {
      return function() {
         $this->addView('ListLogMessage', 'LogMessage', 'log');
      };
   }
}
```

### Ejemplo loadData
Esto sirve para hacer acciones al momento de cargar los datos en las vistas, por ejemplo cargar los logs de la pestaña que antes añadimos en el createviews, pero solo cuando el log sea del tipo error. Para ello creamos el archivo **Extension/Controller/ListProducto**. Este método recibe dos parámetros **$viewName** (lleva solo el nombre en formato string de la pestaña que se está ejecutando en ese momento) y **$view** (lleva un objeto con los datos de la pestaña).
```
<?php
namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
   public function loadData(): Closure
   {
      return function($viewName, $view) {
				if ($viewName === 'ListLogMessage') {
					$where = [new DataBaseWhere('tipo', 'error')];
					$view->loadData('', $where);
					break;
				}
      };
   }
}
```

### Ejemplo execPreviousAction
Esto sirve para ejecutar una acción antes de la ejecución del controlador, por ejemplo para capturar que hacer al clicar un botón. Recibe como parámetro un string **$action** con el nombre de la acción.
```
<?php
namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
   public function execPreviousAction(): Closure
   {
      return function($action) {
				if ($action === 'clic-button') {
					// aquí ponemos nuestro código para ejecutar con está acción
					return;
				}
      };
   }
}
```

### Ejemplo execAfterAction
Esto sirve para ejecutar una acción después de la ejecución del controlador, por ejemplo para capturar que hacer al clicar un botón. Recibe como parámetro un string **$action** con el nombre de la acción.
```
<?php
namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
   public function execAfterAction(): Closure
   {
      return function($action) {
				if ($viewName === 'clic-button') {
					// aquí ponemos nuestro código para ejecutar con está acción
					return;
				}
      };
   }
}
```

### Ejemplo selectAction
Esto sirve para devolver los datos que debe llevar un select, debe devolver siempre un array con los datos clave=valor. Recibe como parámetros **$data** (un array con los datos recibidos por get/post) y **$required** (para indicar si el select es de tipo requerido o no).
```
<?php
namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
   public function selectAction(): Closure
   {
      return function($data, $required) {
				$results = [];
        foreach ($this->codeModel->all($data['source'], $data['fieldcode'], $data['fieldtitle'], !$required, $where) as $value) {
            // no usar fixHtml() aquí porque compromete la seguridad
            $results[] = ['key' => $value->code, 'value' => $value->description];
        }
        return $results; 
      };
   }
}
```

Recuerda que las funciones deben tener un **return function()** y que debes cargar la extensión desde el archivo **Init.php** del plugin.

### Cargar extensiones en el Init.php
Las extensiones de archivos xml se integran automáticamente al activar el plugin o reconstruir Dinamic. En cambio, las extensiones de archivos php se deben cargar explícitamente llamando al método ``loadExtension()`` del [archivo Init.php del plugin](/publicaciones/el-archivo-init-php-307), en el método **init()**.

```
public function init(): void
{
   // cargamos la extensión del controlador ListProducto
   $this->loadExtension(new Extension\Controller\ListProducto());
}
```

## fsmaker
Para hacer este mismo proceso con [fsmaker](/publicaciones/fsmaker-0-92-disponible) ejecutamos:

```
fsmaker extension
```

En el asistente elegimos controlador y escribimos el nombre del controlador.

## ¿No funciona?
Los errores más comunes son:
- **Tener un namespace incorrecto**. El namespace debe reflejar la ruta donde está el archivo. Si el archivo está en la carpeta Extension/Model de tu plugin, el namespace debe incluir Extension\Model.
- **Intentar extender funciones que no soportan extensiones**. Solamente las funciones o métodos indicados arriba se pueden extender.
- **No usar return function()**. Las funciones deben devolver un return function(), y si no, no funcionará. Si la función necesita parámetros, estos deben ir en el return function().
- **No cargar la extensión desde el Init.php del plugin**.

### Parámetros por referencia
Si crear funciones personalizadas y estas incluyen parámetros no se debe poner "&" en los parámetros ya que no está permitido, y causará problemas. Como sugerencia puedes devolverte el parámetro que deseas modificar.

**Ejemplo mal**
```
public function applyStockChangesFromWork(): Closure
{
		return function (&$stock) {
			$stock->cantidad = 5;
		};
}
```

**Ejemplo Bueno**
```
public function applyStockChangesFromWork(): Closure
{
		return function ($stock) {
			$stock->cantidad = 5;
			return $stock;
		};
}
```
