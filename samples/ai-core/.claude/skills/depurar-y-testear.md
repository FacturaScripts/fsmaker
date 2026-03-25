---
name: depurar-y-testear
description: Guía para depurar, testear y verificar código en FacturaScripts usando el modo debug, logs y PHPUnit.
triggers:
  - depurar
  - debug
  - testear
  - crear test
  - error log
  - modo debug
---

# Skill: Depurar y Testear en FacturaScripts

## Activar el modo debug

Editar `config.php` en la raíz de FacturaScripts:

```php
define('FS_DEBUG', true);
```

Esto activa la barra de debug en la parte inferior derecha con información de queries, logs y errores.

## Mostrar mensajes y errores

```php
use FacturaScripts\Core\Tools;

// Info (mensaje informativo)
Tools::log()->info('Mensaje informativo');

// Warning
Tools::log()->warning('Atención: algo no está bien');

// Error
Tools::log()->error('Ha ocurrido un error');

// Error con traducción
Tools::log()->error('campo-obligatorio', ['%field%' => 'Nombre']);

// Mostrar en la interfaz (toast)
Tools::log('audit')->info('Acción completada');
```

## Ver logs

Los logs se almacenan en la tabla `logmessages` y se pueden ver desde **Administrador → Log**.

## Reconstruir Dinamic

Cuando los cambios no se reflejan, hacer clic en **Administrador → Plugins → Reconstruir** o activar/desactivar el plugin.

## Tests unitarios con PHPUnit

Archivo: `Test/MiPluginTest.php`

```php
<?php

namespace FacturaScripts\Test\Plugins;

use FacturaScripts\Plugins\MiPlugin\Model\NombreModelo;
use PHPUnit\Framework\TestCase;

final class NombreModeloTest extends TestCase
{
    public function testCrearRegistro(): void
    {
        $modelo = new NombreModelo();
        $modelo->name = 'Test';
        $this->assertTrue($modelo->save());
        $this->assertNotEmpty($modelo->id);

        // Limpiar
        $this->assertTrue($modelo->delete());
    }

    public function testValidacion(): void
    {
        $modelo = new NombreModelo();
        $modelo->name = ''; // Inválido
        $this->assertFalse($modelo->save());
    }
}
```

Ejecutar los tests:

```bash
composer test
# O directamente:
vendor/bin/phpunit
```

## Verificar estilo de código

```bash
# Verificar
composer cs-check

# Corregir automáticamente
composer cs-fix

# Análisis estático
composer phpstan
```

## Errores comunes y soluciones

| Error | Causa | Solución |
|-------|-------|----------|
| Clase no encontrada | Namespace incorrecto | Verificar que el namespace coincide con la ruta del archivo |
| Vista no carga | XMLView con nombre incorrecto | El nombre del XMLView debe coincidir exactamente con el viewName |
| Tabla no creada | XML de tabla con errores | Verificar sintaxis del XML en `Table/` |
| Cambios sin efecto | Cache no limpiada | Reconstruir Dinamic desde Administrador → Plugins |
| Extensión no se aplica | No cargada en Init.php | Añadir `$this->loadExtension()` en `Init::init()` |

## Documentación relacionada
- `.claude/docs/gestion-de-errores.md`
- `.claude/docs/testeo-de-plugins.md`
- `.claude/docs/mostrar-mensajes-errores-y-alertas.md`
