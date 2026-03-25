# Cómo guardar una cookie

> **ID:** 616 | **Permalink:** guardar-una-cookie-994 | **Última modificación:** 04-04-2025
> **URL oficial:** https://facturascripts.com/guardar-una-cookie-994

Para guardar o modificar una cookie, debemos utilizar los objetos `Cookie` y `response` del controlador.

## Cargar el Namespace
Antes de crear o modificar una cookie, es necesario declarar que vamos a usar la clase `Cookie`. Para ello, debes incluir la siguiente línea justo debajo del namespace:

```
use Symfony\Component\HttpFoundation\Cookie;
```

## Ejemplo: Guardar una cookie
En este ejemplo, vamos a guardar una cookie con el nombre `order1` y el valor `'ASC'`.

```php
$expire = time() + 3600; // Expira en 1 hora
$this->response->headers->setCookie(new Cookie('order1', 'ASC', $expire));
```

## Consideraciones
- **Duración**: La duración de la cookie se define mediante la variable `$expire`. En el ejemplo, se establece para que expire en 1 hora.
- **Accesibilidad**: Asegúrate de que la cookie sea accesible desde las rutas correspondientes de tu aplicación.
