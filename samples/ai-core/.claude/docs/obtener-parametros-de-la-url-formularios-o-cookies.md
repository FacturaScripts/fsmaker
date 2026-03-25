# Obtener Parámetros de la URL, Formularios o Cookies

> **ID:** 615 | **Permalink:** obtener-parametros-de-la-url-formularios-o-cookies-496 | **Última modificación:** 31-12-2025
> **URL oficial:** https://facturascripts.com/obtener-parametros-de-la-url-formularios-o-cookies-496

Todos los **controladores de FacturaScripts** cuentan con la propiedad [request](https://facturascripts.com/publicaciones/objeto-request-como-recibir-datos-de-formularios-url-cookies-etc), que permite acceder a los parámetros de la URL (`$_GET`), formularios (`$_POST`) o cookies (`$_COOKIE`). Algunas de las principales propiedades del objeto **request** son:

- **query** (equivalente a **$_GET**).
- **request** (equivalente a **$_POST**).
- **server** (equivalente a **$_SERVER**).
- **files** (equivalente a **$_FILES**).
- **cookies** (equivalente a **$_COOKIE**).

## ⚡ Obtener un Parámetro de la URL
Supongamos que la URL es `http://example.com/MyNewController?idproject=1` y queremos obtener el valor de `idproject`. Pues simplemente usamos el método [query()](https://facturascripts.com/publicaciones/objeto-request-como-recibir-datos-de-formularios-url-cookies-etc#md_h1):

```php
$value = $this->request->query('idproject');
```

### ⌨️ Obtener el Valor de un Input de un Formulario
Imaginemos que tenemos un formulario con un input llamado `description` y queremos obtener el valor enviado. Podemos obtenerlo llamando al método [input()](https://facturascripts.com/publicaciones/objeto-request-como-recibir-datos-de-formularios-url-cookies-etc#md_h2):

```php
$value = $this->request->input('description');
```

### 🍪 Obtener el Valor Almacenado en una Cookie
Supongamos que hemos guardado una cookie con el nombre `order1` y queremos leer el valor que almacena. Para ello llamamos al método [cookie()](https://facturascripts.com/publicaciones/objeto-request-como-recibir-datos-de-formularios-url-cookies-etc#md_h4):

```php
$value = $this->request->cookie('order1');
```

### 🧭 Obtener la IP del Usuario
Para obtener la dirección IP del usuario, se puede usar el siguiente método:

```php
$ip = $this->request->getClientIp();
```

## 🔜 Redireccionar al Usuario
Se puede redirigir a otra página o controlador usando el método `redirect()` del controlador:

```php
$this->redirect('ListProducto');
```

En este caso, estamos redireccionando al controlador `ListProducto`, que muestra el listado de productos.
