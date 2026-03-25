# Gestión de Errores en FacturaScripts

> **ID:** 1599 | **Permalink:** gestion-de-errores | **Última modificación:** 05-07-2025
> **URL oficial:** https://facturascripts.com/gestion-de-errores

En FacturaScripts, podemos lanzar excepciones que redirigen a páginas de error personalizadas. Para ello, es necesario lanzar una `KernelException` especificando el nombre de la página de error que deseamos mostrar. Por ejemplo, para mostrar una página de error de **Permiso Denegado**, utilizaremos el siguiente código:

```php
throw new KernelException('AccessDenied', 'test');
```

Las páginas de error son archivos PHP que se encuentran en la **carpeta Error**. Esto permite personalizarlas mediante el uso de plugins.

## Ejemplo de Página de Error

A continuación, se presenta un ejemplo de cómo crear una página de error utilizando un plugin:

```php
namespace FacturaScripts\Plugins\MiPlugin\Error;

use FacturaScripts\Core\Template\ErrorController;

class MiError extends ErrorController
{
    public function run(): void
    {
        // Código de ejemplo para mostrar un error
        echo 'ERROR';
    }
}
```

## Consideraciones

- Asegúrate de que el nombre de la excepción y la página de error correspondan a los definidos en tu aplicación.
- Personalizar las páginas de error ayuda a mejorar la experiencia del usuario al comunicar de manera efectiva el motivo del error.
