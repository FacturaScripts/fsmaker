# Cómo Añadir un Endpoint Personalizado a la API

> **ID:** 1604 | **Permalink:** anadir-un-endpoint-a-la-api | **Última modificación:** 22-12-2025
> **URL oficial:** https://facturascripts.com/anadir-un-endpoint-a-la-api

Desde la **versión 2023.1** de FacturaScripts, agregar nuevos endpoints a la API es más sencillo gracias al renovado [sistema de enrutamiento](https://facturascripts.com/publicaciones/profundizando-en-el-core). Solo necesitas crear un controlador que herede de **ApiController** y registrar la ruta en el archivo **Init.php** de tu plugin.

## 1. Registrar la Ruta en el Archivo Init.php
En la función `init()` de tu archivo [Init.php](https://facturascripts.com/publicaciones/el-archivo-init-php-307), añade la nueva ruta y asígnala al controlador correspondiente. Ejemplo:

```php
<?php
namespace FacturaScripts\Plugins\MyNewPlugin;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Core\Controller\ApiRoot;
use FacturaScripts\Core\Kernel;

class Init extends InitClass
{
    public function init(): void
    {
        // Registra la nueva ruta en la API y vincúlala al controlador personalizado
        Kernel::addRoute('/api/3/mi-endpoint', 'ApiControllerPruebas', -1);
        ApiRoot::addCustomResource('mi-endpoint');
    }
}
```

## 2. Crear tu ApiController Personalizado
Debes crear el archivo `ApiControllerPruebas.php` en la carpeta **Controller** de tu plugin. Este controlador gestionará la lógica de tu nuevo endpoint:

```php
<?php
namespace FacturaScripts\Plugins\MyNewPlugin\Controller;

use FacturaScripts\Core\Template\ApiController;

class ApiControllerPruebas extends ApiController
{
    protected function runResource(): void
    {
        // Lógica del endpoint
        $this->response()->json(['hola' => 'mundo']));
    }
}
```

Puedes personalizar la lógica modificando el contenido dentro de `runResource()` según tus necesidades.

## 3. Ejemplo de Consumo del Endpoint
Una vez implementado el endpoint, puedes hacer solicitudes a:

```
https://tusitio.com/api/3/mi-endpoint
```

La respuesta será:

```json
{
    "hola": "mundo"
}
```
