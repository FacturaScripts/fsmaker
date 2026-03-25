---
name: crear-plugin
description: Crea la estructura completa de un nuevo plugin para FacturaScripts con todos los archivos necesarios.
triggers:
  - crear plugin
  - nuevo plugin
  - scaffold plugin
  - iniciar plugin
---

# Skill: Crear Plugin FacturaScripts

Cuando el usuario pida crear un nuevo plugin para FacturaScripts, sigue estos pasos:

## Paso 1: Obtener información del plugin

Pregunta al usuario:
- **Nombre del plugin** (sin espacios, empieza por mayúscula. Ej: `MiPlugin`)
- **Descripción** breve
- **Versión mínima de FacturaScripts** (por defecto: 2025)

## Paso 2: Crear la estructura de carpetas

Crea la siguiente estructura en `Plugins/NombrePlugin/`:

```
Plugins/NombrePlugin/
├── Assets/
│   ├── CSS/
│   ├── Images/
│   └── JS/
├── Controller/
├── Extension/
│   ├── Controller/
│   ├── Model/
│   └── View/
├── Model/
├── Table/
├── Translation/
├── View/
├── XMLView/
├── facturascripts.ini
└── Init.php
```

## Paso 3: Crear facturascripts.ini

```ini
name = 'NombrePlugin'
description = 'Descripción del plugin'
version = 1
min_version = 2025
```

## Paso 4: Crear Init.php

```php
<?php

namespace FacturaScripts\Plugins\NombrePlugin;

use FacturaScripts\Core\Template\InitClass;

class Init extends InitClass
{
    public function init(): void
    {
        // Cargar extensiones aquí
        // $this->loadExtension(new Extension\Model\MiModelo());
    }

    public function uninstall(): void
    {
        // Limpieza al desinstalar
    }

    public function update(): void
    {
        // Cambios al instalar/actualizar
    }
}
```

## Reglas importantes
- El nombre del plugin NO puede empezar por número ni contener espacios
- El namespace siempre es `FacturaScripts\Plugins\NombrePlugin`
- Nunca modificar archivos de `Core/` ni `Dinamic/`
- Activar el plugin desde Administrador → Plugins tras crearlo

## Documentación relacionada
- `.claude/docs/creacion-de-plugins.md`
- `.claude/docs/el-archivo-facturascripts-ini.md`
- `.claude/docs/el-archivo-init-php.md`
