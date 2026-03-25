# Cliente HTTP de FacturaScripts

> **ID:** 1602 | **Permalink:** cliente-http | **Última modificación:** 02-06-2025
> **URL oficial:** https://facturascripts.com/cliente-http

Tenemos un **cliente HTTP** que puedes utilizar para consultar APIs, descargar contenido, consultar otras webs, etc. Simplifica mucho el código en comparación con CURL.

## Haciendo una consulta
En este ejemplo consultaremos nuestra web y almacenaremos el resultado (el html) en la variante $html.

```
use FacturaScripts\Core\Http;

$html = Http::get('http://facturascripts.com')->body();
```

### Porcesar JSON
Si vamos a consultar una web que devuelve json, por ejemplo una API, podemos llamar directamente al método ``json()`` en lugar de a ``body()``, esto hace que se procese el JSON devuelto.

```
$json = Http::get('https://randomuser.me/api/')->json(false); // devuelve un objeto
$jsonArray = Http::get('https://randomuser.me/api/')->json(); // devuelve un array
```

### Obtener las cabeceras
Podemos obtener todas las cabeceras con la función ``headers()`` o una concretra con la función ``header()``.

```
$request = Http::get(https://movie-quote-api.herokuapp.com/v1/quote/');

$headers = $request->headers(); // obtenemos todas las cabeceras

$total = $request->header('x-total'); // obtenemos la cabecera 'x-total'
```

### ⚠️ Control de errores
Tenemos una serie de funciones que podemos usar para comprobar si la petición ha devuelto errores o no, y cuales. Las funciones son ``ok()``, ``failed()``, ``notFound()``, ``errorMessage()`` y ``status()``.

```
$request = Http::get('https://randomuser.me/api/');

if ($request->ok()) {
	// la respuesta es correcta, podemos consultar los datos con body()
	echo $request->body();
}

if ($request->failed()) {
	// la respuesta no es correcta, podemos consultar el error con errorMessage()
	echo $request->errorMessage();
	
	// también el código de error
	echo $request->status();
}

if ($request->notFound()) {
	// la respuesta no es correcta, ha devuelto código 404
}
```

### ⬇️ Descargar archivos
Si deseas no solo consultar una url, sino almacenar la respuesta en un archivo, es decir, descargar ese archivo a disco. Puedes usar la función ``saveAs()``.

```
Http::get('https://facturascripts.com/PluginInfoList')
	->saveAs('lista.json'); // devuelve true si se descarga correctamente
```

En este caso se guarda en el archivo lista.json de la carpeta de FacturaScripts.

### ⏱️ Establecer timeout
Podemos establecer un tiempo máximo de ejecución con la función ``setTimeout()``.

```
$json = Http::get('https://randomuser.me/api/')
	->setTimeout(10)
	->json();
```

### Añadir cabeceras
Podemos añadir una cabecera a la petición con la función ``setHeader()`` o varias con la función ``setHeaders()``.

```
$json = Http::get('https://tu-api-com/recurso')
	->setHeader('mi-cabecera', 'mi-valor')
	->json();

$json2 = Http::get('https://tu-api-com/recurso')
	->setHeaders([
		'mi-cabecera-1', 'mi-valor-1',
		'mi-cabecera-2', 'mi-valor-2'
	])
	->json();
```

### 🔑 Añadir token
Podemos añadir un token en la cabecera de la petición con el método ``setToken()``.

```
$json = Http::get('https://facturascripts.com/api/3/')
	->setToken('mi-token')
	->json();

// esto sería equivalente
$json = Http::get('https://facturascripts.com/api/3/')
	->setHeader('Token', 'mi-token')
	->json();
```

Para enviar una cabecera de tipo `Authorization: Bearer` podemos usar el método ``setBearerToken()``.

```
$json = Http::get('https://api.openai.com/v1/chat/completions')
	->setBearerToken('mi-bearer-token')
	->json();

// esto sería equivalente
$json = Http::get('https://api.openai.com/v1/chat/completions')
	->setBearerToken('mi-bearer-token')
	->json();
```

### 👤 Establecer usuario y contraseña
Si queremos usar un usuario y contraseña, podemos usar la función ``setUser()``.

```
$json = Http::get('https://tu-web-con-user.com/servicio')
	->setUser('mi-usuario', 'mi-contraseña')
	->json();
```

### Hacer una petición post
Podemos hacer una petición POST, es decir, enviar datos como si fuese un formulario, llamando a la función post en lugar de a get.

```
// enviamos los datos como un formulario
$json = Http::post('https://tu-api-com/recurso', [
		'dato1' => 'valor1',
		'dato2' => 'valor2'
	])
	->json(); // recibimos como json y lo convertimos en array asociativo
```

Si queremos enviar los datos en formato json, podemos usar el método ``postJson()``, que convierte el array de datos a json y lo envía con la cabecera correspondiente:

```
// enviamos los datos en formato json
$json = Http::postJson('https://tu-api.com/recurso', [
		'dato1' => 'valor1',
		'dato2' => 'valor2'
	])
	->json(); // recibimos como json y lo convertimos en array asociativo
```

También tenemos disponibles las funciones ``put()`` y ``delete()`` para hacer las correspondientes peticiones.

### 📎 Enviar archivos
Para enviar archivos por formulario podemos procesarlos previamente con `CURLFile`:

```
// enviamos un archivo
$file_path = 'RUTA DEL ARCHIVO';
$file = new CURLFile($file_path, mime_content_type($file_path), 'NOMBRE DEL ARCHIVO');
$json = Http::post('https://tu-api.com/recurso', [
		'file' => $file
	])
	->setHeader('Content-Type', 'multipart/form-data')
	->json(); // recibimos como json y lo convertimos en array asociativo
```
