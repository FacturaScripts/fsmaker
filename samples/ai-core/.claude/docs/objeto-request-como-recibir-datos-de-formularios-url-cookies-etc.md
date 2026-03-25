# La clase Request: cómo recibir datos de formularios, url, cookies, etc ...

> **ID:** 2224 | **Permalink:** objeto-request-como-recibir-datos-de-formularios-url-cookies-etc | **Última modificación:** 06-10-2025
> **URL oficial:** https://facturascripts.com/objeto-request-como-recibir-datos-de-formularios-url-cookies-etc

La clase Request se encarga de gestionar toda la información de las peticiones HTTP entrantes. Proporciona una interfaz orientada a objetos para acceder a los datos de $_GET, $_POST, $_COOKIE, $_FILES y $_SERVER.

Fichero: [Core/Request.php](https://github.com/NeoRazorX/facturascripts/blob/master/Core/Request.php)

Este objeto está disponible en **todos los controladores**, ya sea como propiedad o como método.

```
// controladores actuales
$mi_campo = $this->request->input('mi_campo');

// para los nuevos controladores
$mi_campo = $this->request()->input('mi_campo');
```

## 🖱️ Obtener parámetros de la url (query)
En ocasiones queremos obtener un parámetro que nos llega en la url, por ejemplo esta:

- http ... /MiControlador?`id=1234`

Para obtener el valor del parámetro id debemos usar el método `query()`:

```
$id = $this->request()->query('id');

// método alternativo
$id = $this->request()->query->get('id');

// todos los parámetros de la url
$all = $this->request()->query->all();
```

## ⌨️ Obtener valores del formulario (input)
Para obtener el valor de un campo que nos llega por formulario debemos usar el método `input()`, que obtiene el parámetro de la entrada request (POST/PUT/PATCH).

```
$mi_campo = $this->request()->input('mi_campo');

// método alternativo
$mi_campo = $this->request()->request->get('mi_campo');

// todos los campos
$all = $this->request()->request->all();
```

### 🔍 Obtener valores de url y formularios
En ocasiones un parámetro podemos recibirlo por la url o bien por formulario. En estos casos tenemos dos métodos para establecer la prioridad:

- `inputOrQuery()`: consulta primero el valor de input y si no existe entonces devuelve el de query (url).
- `queryOrInput()`: consulta primero el valor de query (url) y si no existe entonces devuelve el de input.
- `get()`: consulta primero el valor de query (url) y si no existe entonces devuelve el de query. **Obsoleto**.

Para este ejemplo recibiremos por la url el parámetro `mi_campo=555` y por formulario nos llega `mi_campo=777`:

```
$mi_campo = $this->request()->inputOrQuery('mi_campo'); // 777

$mi_campo = $this->request()->queryOrInput('mi_campo'); // 555

$mi_campo = $this->request()->get('mi_campo'); // 555
```

## 🍪 Cookies
Obtiene el valor de una cookie específica.

```
$mi_cookie = $this->request()->cookie('mi_cookie');

// método alternativo
$mi_cookie = $this->request()->cookies->get('mi_cookie');

// todas las cookies
$cookies = $this->request()->cookies->all();
```

## ✉️ Cabeceras (header)
Para obtener el valor de una cabecera de la petición HTTP debemos usar el método `header()`:

```
$mi_header = $this->request()->header('mi_header');

// método alternativo
$mi_header = $this->request()->headers->get('mi_header');

// todas las cabeceras
$all = $this->request()->headers->all();
```

## 📦 json
Para obtener un json recibido debemos usar el método `json()`, que nos devuelve el json ya convertido en array asocuativo:

```
$json = $this->request()->json();
```

Si se especifica $key, devuelve solo ese campo del JSON o el valor por defecto:

```
$name = $this->request()->json('name'); // devuelve solo el campo 'name'
$age = $this->request()->json('age', 0);    // devuelve 'age' o 0 si no existe
```

## 🧾 getContent
Devuelve el cuerpo crudo de la petición HTTP. Es útil para peticiones XML o cualquier contenido que no sea form-data:

```
$raw = $this->request()->getContent();
```

## Otros métodos Públicos

### static createFromGlobals(): self
Método factoría que crea una instancia de Request a partir de las variables globales de PHP ($_COOKIE, $_FILES, $_SERVER, $_GET, $_POST).

```
$request = Request::createFromGlobals();
```

### all(string ...$key): array
Devuelve un array con todos los parámetros de la petición (query y request). Si se especifican claves,
devuelve un array asociativo con los valores de esas claves.

```
$all = $this->request()->all();

// solamente algunos campos
$some = $this->request()->all('campo1', 'campo2', 'campo3');
```

### browser(): string
Detecta y devuelve el navegador del cliente a partir del User-Agent. Puede devolver: chrome, edge, firefox, safari, opera, ie o unknown.

```
$browser = $this->request()->browser(); // firefox

// si prefieres el user-agent completo
$some = $this->request()->userAgent();
```

### 📄 file(string $key): ?UploadedFile
Obtiene un fichero subido por su clave. Devuelve un objeto UploadedFile.

```
$upload_file = $this->request()->file('mi_archivo');

// método alternativo
$upload_file = $this->request()->files->get('mi_archivo');

// para varios archivos
$upload_files = $this->request()->files->getArray('mi_archivo');
```

### has(): bool
Si necesitas consultar si un parámetro existe, ya llegue por url o por formulario, puedes usar el método `has()`:

```
if ($this->request()->has('mi_campo') {
	// existe ese campo
}

// comprobamos solamente en la url
if ($this->request()->query->has('mi_campo') {
	// existe ese campo
}

// comprobamos solamente por input
if ($this->request()->request->has('mi_campo') {
	// existe ese campo
}

// comprobamos en las cookies
if ($this->request()->cookies->has('mi_campo') {
	// existe ese campo
}

// comprobamos en las cabeceras
if ($this->request()->headers->has('mi_campo') {
	// existe ese campo
}
```

### host(): string
Devuelve el host de la petición.

```
$host = $this->request()->host();
```

### ip(): string
Devuelve la dirección IP del cliente. Tiene en cuenta cabeceras de proxy como HTTP_CF_CONNECTING_IP y HTTP_X_FORWARDED_FOR.

```
$ip = $this->request()->ip();
```

### isMethod(string $method): bool
Comprueba si el método de la petición es el especificado.

```
if ($this->request()->isMethod(Request::METHOD_POST)) {
	// es una petición POST
}
```

### method(): string
Devuelve el método HTTP de la petición (GET, POST, PUT, etc.).

```
$method = $this->request()->method();
```

### os(): string
Detecta y devuelve el sistema operativo del cliente a partir del User-Agent. Puede devolver: windows, mac, linux, unix, sun, bsd o unknown.

```
$os = $this->request()->os(); // linux
```

### protocol(): string
Devuelve el protocolo de la petición (ej: HTTP/1.1).

```
$protocol = $this->request()->protocol();
```

### 🔒 isSecure(): bool
Devuelve true si la petición se ha realizado a través de HTTPS.

```
if ($this->request()->isSecure()) {
	// es una petición HTTPS
}
```

### url(?int $position = null): string
Devuelve la URL de la petición sin la query string. Si se proporciona una posición, devuelve la parte de la URL correspondiente a esa posición (separada por /).

```
// http://localhost/MiControlador/1234/?param1=555
$url = $this->request()->url(); // http://localhost/MiControlador
$id = $this->request()->url(1); // 1234
```

### urlWithQuery(): string
Devuelve la URL con la query string.

```
$url = $this->request()->urlWithQuery();
```

### userAgent(): string
Devuelve el User-Agent de la petición.

```
$user_agent = $this->request()->userAgent();
```
