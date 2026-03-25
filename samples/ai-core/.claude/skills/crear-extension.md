---
name: crear-extension
description: Crea extensiones de FacturaScripts para modificar modelos, controladores o vistas del core o de otros plugins sin modificar su código fuente.
triggers:
  - crear extensión
  - extender modelo
  - extender controlador
  - modificar core
  - extensión plugin
---

# Skill: Crear Extensión FacturaScripts

Las extensiones permiten añadir funcionalidad a clases del core o de otros plugins **sin modificar su código fuente**.

## Extensión de Modelo

Archivo: `Plugins/MiPlugin/Extension/Model/NombreModelo.php`

```php
<?php

namespace FacturaScripts\Plugins\MiPlugin\Extension\Model;

use Closure;

class NombreModelo
{
    // Se ejecuta ANTES de guardar el modelo
    public function saveInsert(): Closure
    {
        return function () {
            // $this es el modelo original
            // Código a ejecutar antes de insertar
        };
    }

    // Se ejecuta DESPUÉS de guardar
    public function saveUpdate(): Closure
    {
        return function () {
            // Código a ejecutar después de actualizar
        };
    }

    // Se ejecuta al eliminar
    public function delete(): Closure
    {
        return function () {
            // Código a ejecutar al eliminar
        };
    }

    // Se ejecuta en test() (validación)
    public function test(): Closure
    {
        return function () {
            // Añadir validaciones extra
            // Retorna void, el pipe continúa
        };
    }
}
```

## Extensión de Controlador

Archivo: `Plugins/MiPlugin/Extension/Controller/ListProducto.php`

```php
<?php

namespace FacturaScripts\Plugins\MiPlugin\Extension\Controller;

use Closure;

class ListProducto
{
    public function createViews(): Closure
    {
        return function () {
            // Añadir nueva pestaña al listado de productos
            $this->addView('ListMiModelo', 'MiModelo', 'mi-modelo')
                ->addOrderBy(['name'], 'name')
                ->addSearchFields(['name']);
        };
    }
}
```

## Extensión de XMLView

Archivo: `Plugins/MiPlugin/Extension/XMLView/EditProducto.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <column name="mi-campo" order="500">
            <widget type="text" fieldname="mi_campo" />
        </column>
    </columns>
</view>
```

Los XMLView de extensión se fusionan automáticamente con el original.

## Extensión de Tabla

Archivo: `Plugins/MiPlugin/Extension/Table/productos.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<table>
    <column>
        <name>mi_campo</name>
        <type>character varying(100)</type>
    </column>
</table>
```

Las extensiones de tabla también se fusionan automáticamente.

## Cargar extensiones PHP en Init.php

Las extensiones de PHP (modelos y controladores) **deben cargarse** en `Init.php`:

```php
public function init(): void
{
    // Extensiones de modelos
    $this->loadExtension(new Extension\Model\Producto());
    $this->loadExtension(new Extension\Model\Cliente());

    // Extensiones de controladores
    $this->loadExtension(new Extension\Controller\ListProducto());
    $this->loadExtension(new Extension\Controller\EditProducto());
}
```

## Reglas importantes
- Las extensiones XML (tablas, XMLViews) se cargan automáticamente
- Las extensiones PHP deben registrarse en `Init.php`
- No es herencia: múltiples plugins pueden extender la misma clase
- No se pueden extender clases de `Core/Base` ni `Core/Lib/ExtendedController`

## Documentación relacionada
- `.claude/docs/las-extensiones.md`
- `.claude/docs/extensiones-de-modelos.md`
- `.claude/docs/extensiones-de-controladores.md`
- `.claude/docs/extensiones-de-tablas.md`
- `.claude/docs/extensiones-de-xmlview.md`
