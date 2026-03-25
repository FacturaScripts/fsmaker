# La clase Response: cómo devolver datos

> **ID:** 2225 | **Permalink:** objeto-response-como-devolver-datos | **Última modificación:** 06-10-2025
> **URL oficial:** https://facturascripts.com/objeto-response-como-devolver-datos

La clase Response se utiliza para construir y enviar una respuesta HTTP al cliente. Permite establecer el código de estado HTTP, las cabeceras, las cookies y el contenido de la respuesta.

Fichero: [Core/Response.php](https://github.com/NeoRazorX/facturascripts/blob/master/Core/Response.php)

Este objeto está disponible en **todos los controladores**, ya sea como propiedad o como método.

```php
// controladores actuales
$this->response->json(['mis_datos' => '1234']);

// para los nuevos controladores
$this->response()->json(['mis_datos' => '1234']);
```

## Constantes de Código de Estado HTTP
- HTTP_OK (200)
- HTTP_BAD_REQUEST (400)
- HTTP_UNAUTHORIZED (401)
- HTTP_FORBIDDEN (403)
- HTTP_NOT_FOUND (404)
- HTTP_METHOD_NOT_ALLOWED (405)
- HTTP_INTERNAL_SERVER_ERROR (500)

## Propiedades Públicas
- `$headers`: Un objeto ResponseHeaders que gestiona las cabeceras de la respuesta.

## Métodos Públicos

### header(string $name, string $value): self
Establece una cabecera HTTP para la respuesta.

```php
// método normal
$this->response->header('Content-Type', 'application/json');

// método alternativo
$this->response->headers->set('Content-Type', 'application/json');
```

### setHttpCode(int $http_code): self
Establece el código de estado HTTP para la respuesta.

```php
$this->response->setHttpCode(403);
```

### getHttpCode(): int
Devuelve el código de estado HTTP de la respuesta.

```php
$http_code = $this->response->getHttpCode();
```

### setContent(string $content): self
Establece el contenido del cuerpo de la respuesta.

```php
$this->response->setContent('Hola mundo');
```

### getContent(): string
Devuelve el contenido actual de la respuesta.

```php
$content = $this->response->getContent();
```

### ↪️ redirect(string $url, int $delay = 0): self
Prepara una redirección a otra URL. Pero no se envía.

- `$url`: La URL a la que se va a redirigir.
- `$delay`: Si es mayor que 0, usa la cabecera Refresh para una redirección retardada. Si no, usa Location para una redirección inmediata (código 302).

```php
$this->response->redirect('Dashboard')->send();
```

### 🍪 cookie(string $name, string $value, int $expire = 0, bool $httpOnly = true, ?bool $secure = null, string $sameSite = 'Lax'): self
Añade una cookie a la respuesta.

- `$name`: Nombre de la cookie.
- `$value`: Valor de la cookie.
- `$expire`: Timestamp de expiración. Si es 0, usa el valor de configuración cookies_expire.
- `$httpOnly`: Si es true, la cookie solo será accesible a través del protocolo HTTP.
- `$secure`: Si es true, la cookie solo se enviará sobre conexiones seguras (HTTPS). Si es null, se autodetecta.
- `$sameSite`: Controla cuándo se envía la cookie (Lax, Strict, None).

```php
// creo la cookie 'hola' con el valor 'mundo'
$this->response->cookie('hola', 'mundo');
```

### 🍪 withoutCookie(string $name): self
Indica al navegador que elimine una cookie estableciendo su tiempo de expiración en el pasado.

```php
// elimino la cookie session
$this->response->withoutCookie('session');
```

### json(array $data): void
Prepara y envía una respuesta en formato JSON. Establece la cabecera Content-Type a application/json.

```php
$this->response->json(['hola' => 'mundo']);
```

### view(string $view, array $data = []): void
Renderiza una vista de plantilla y la establece como contenido de la respuesta. Envía la respuesta con Content-Type: text/html.

```php
$this->response->view('MyView'); // View/MyView.html.twig
```

### 📄 file(string $file_path, string $file_name = '', string $disposition = 'inline'): void
Envía un fichero como respuesta.

- `$file_path`: Ruta al fichero en el servidor.
- `$file_name`: Nombre que se sugerirá al cliente para el fichero.
- `$disposition`: Tipo de disposición (inline para mostrar en el navegador, attachment para descargar).

```php
// mostrar el archivo con opción de descarga
$this->response->file(FS_FOLDER . '/MyFiles/public/miArchivo.pdf', 'factura1.pdf', 'inline');

// descargar el archivo directamente
$this->response->file(FS_FOLDER . '/MyFiles/public/miArchivo.pdf', 'factura1.pdf', 'attachment');
```

### ⬇️ download(string $file_path, string $file_name = ''): void
Prepara una respuesta para forzar la descarga de un fichero. Establece las cabeceras Content-Disposition a attachment.

```php
// descargar el archivo
$this->response->download(FS_FOLDER . '/MyFiles/public/fatura-230.xml', 'fatura230.xml');
```

### pdf(string $content, string $file_name = ''): void
Prepara y envía una respuesta con contenido PDF. Establece las cabeceras apropiadas para mostrar un PDF en el navegador.

```php
// leer su contenido
$miContenidoPdf = file_get_contents(FS_FOLDER . '/MyFiles/public/miArchivo.pdf');

// mostrarlo
$this->response->pdf($miContenidoPdf, 'miArchivo.pdf');
```

### send(): void
Envía la respuesta completa (código de estado, cabeceras, cookies y contenido) al cliente.

```php
// envio una respuesta 'OK' en formato texto y escribo la respuesta con send().
$this->response
    ->setContent('OK')
    ->header('Content-Type', 'text/plain')
    ->send();

// normalmente 'send()' se llama automáticamente dependiendo del método de Response
// revisar Core/Response.php para ver en qué métodos se llama 'send()'
```

### 🚫 disableSend(bool $disable = true): self
Deshabilita el envío de la respuesta. Útil para tests o para manipular la respuesta antes de enviarla.

```php
// desactivo que se envie la respuesta
$this->response->disableSend();

// preparo una respuesta
$this->response->json(['hola' => 'mundo']);

// reactivo la respuesta
$this->response->disableSend(false);

// envio la respuesta
$this->response->send();
```
