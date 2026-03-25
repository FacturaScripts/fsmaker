# Consultar el usuario actual y sus permisos en FacturaScripts

> **ID:** 617 | **Permalink:** usuario-y-permisos-442 | **Última modificación:** 04-06-2025
> **URL oficial:** https://facturascripts.com/usuario-y-permisos-442

La clase **Session** de la carpeta *Core* permite consultar y almacenar información del usuario actual desde controladores, modelos y otras clases. Esta herramienta también facilita añadir temporalmente información accesible durante la ejecución actual de la aplicación.

## Añadir información a la sesión
Puedes guardar información personalizada en la sesión usando el método `Session::set()`:

```php
$data = ['color1' => 'rojo', 'color2' => 'verde'];
Session::set('colores', $data);
```

> **Nota:** Esta información solo estará disponible durante la ejecución actual.

## Obtener el usuario actual
Para acceder al usuario autenticado actual, utiliza:

```php
$user = Session::user();
if ($user) {
    Tools::log()->notice($user->nick);
}
```

### Desde un controlador
En los controladores puedes acceder directamente con:

```php
Tools::log()->notice($this->user->nick);
```

> **Alternativa:** En los controladores, la variable `$this->user` almacena el usuario actual (es el mismo usuario guardado en la sesión) y solo puede usarse dentro de los controladores.

## Consultar permisos del usuario actual
La clase `User` (objeto retornado por Session) dispone del método `can()` para consultar permisos sobre controladores específicos.

```php
$user = Session::user();

if ($user && $user->can('EditPedidoCliente')) {
    Tools::log()->notice('El usuario puede acceder a la edición de pedidos de cliente.');
}

if ($user && $user->can('EditPedidoCliente', 'update')) {
    Tools::log()->notice('El usuario puede editar pedidos de cliente.');
}
```

### Parámetros del método `can()`
- **$pageName** (obligatorio): nombre del controlador a comprobar.
- **$permission** (opcional): permiso específico a consultar (por defecto es "access"). Permisos disponibles: 
  - `access` (acceso),
  - `delete` (eliminar),
  - `export` (exportar/imprimir),
  - `import` (importar),
  - `update` (modificar),
  - `only-owner-data` (solo ver datos propios).

#### Ejemplo de uso en vistas o plugins personalizados
En pestañas o listados donde solo ciertos usuarios pueden acceder a controladores como EditPresupuestoCliente, EditPedidoCliente, etc., se recomienda consultar los permisos antes de mostrar la información.

### Consulta rápida de permisos en el controlador
La propiedad `$this->permissions` de los controladores resume los permisos del usuario para ese controlador:

```php
var_dump($this->permissions);
```

Propiedades principales:
- `allowAccess` (bool): acceso permitido.
- `allowDelete` (bool): permiso para eliminar.
- `allowUpdate` (bool): permiso para modificar.
- `allowExport` (bool): permiso para exportar/imprimir.
- `allowImport` (bool): permiso para importar datos.
- `onlyOwnerData` (bool): sólo ver datos propios.
- `accessMode` (int): nivel de acceso.

> **Nota:** `$this->permissions` solo está disponible en los controladores.

## Modificar permisos del usuario actual en el controlador
Si necesitas alterar los permisos para el controlador en ejecución, usa el método `set()` en `$this->permissions`:

```php
// allowAccess, accessMode, allowDelete, allowUpdate, onlyOwnerData
$this->permissions->set(true, 99, true, true, false);
```

Esto te permite personalizar los permisos del usuario para el controlador actual de forma puntual, por ejemplo, para conceder acceso temporal:

```php
// Conceder acceso, con nivel 2, sin permisos de eliminar ni modificar
$this->permissions->set(true, 2, false, false, false);
```

Esta técnica es útil cuando deseas otorgar permisos extras en extensiones o funcionalidades específicas sin modificar la configuración global de roles/permisos.
