# Uso de la caché

> **ID:** 1580 | **Permalink:** uso-de-la-cache | **Última modificación:** 26-08-2025
> **URL oficial:** https://facturascripts.com/uso-de-la-cache

La caché es un **almacén de memoria temporal**. En ella podemos almacenar la información que queramos para luego recuperarla. Usaremos una clave o identificador para aquello que queramos almacenar o leer. Por ejemplo, si queremos almacenar el número de familias, lo podríamos almacenar en la clave ``num-familias``. Así luego podemos leer el valor de esa clave y obtener el valor que hemos almacenado.

No olvide añadir el correspondiente use de la clase.

```
use FacturaScripts\Core\Cache;
```

FacturaScripts utiliza un sistema propio de caché en archivos, que se guardan en la carpeta **MyFiles/Tmp/FileCache**. No utilizamos memcached, ni ninguna otra implementación.

## Cache::set()
Con la función ``set()`` podemos almacenar un valor que queramos en una clave que indiquemos.

```
Cache::set('mis-cosas', 'mi-valor'); // almacenamos el valor 'mi-valor' en la clave 'mis-cosas'
```

## Cache::get()
Con la función ``get()`` podemos obtener el valor almacenado en la clave que indiquemos.

```
echo Cache::get('mis-cosas'); // esto imprime en pantalla 'mi-valor', que es lo que hemos almacenado antes
```

### cache() en twig
También podemos leer de caché desde plantillas twig. Para ello usaremos la función ``cache()``, que es el equivalente a ``Cache::get()``.

```
{{ cache('mis-cosas') }}
```

### Cache::remember()
En muchas ocasiones leeremos de caché y si no encontramos lo que queremos, entonces lo leemos de la base de datos y a continuación lo almacenamos en caché. Este proceso lo podemos simplificar con la función ``remember()``, que hace precisamente eso: si el dato se encuentra en caché, lo devuelve y si no, almacena lo que le digamos y lo devuelve.

```
$numFamilias = Cache::remember('num-familias', function () {
	$familia = new Familia();
	return $familia->count();
});
```

En este ejemplo estamos consultando en la caché el valor de la clave 'num-familias'. Si no lo encuentra, entonces ejecutará el callback, que obtiene el número de familias y lo devuelve. La función remember se encargará de almacenarlo en caché.

## Cache::delete()
Para eliminar un valor de una clave podemos llamar a la función ``delete()``.

```
Cache::delete('mis-cosas');
```

### Cache::deleteMulti()
Si queremos eliminar múltiples claves a la vez, por ejemplo todas la claves que comiencen por "mis-", podemos llamar a la función ``deleteMulti()``.

```
Cache::deleteMulti('min-'); // borra todas las claves que comiencen por "mis-"
```

### Cache::clear()
También podemos eliminar todo el contenido de caché con la función ``clear()``.

```
Cache::clear(); // eliminamos todo
```
