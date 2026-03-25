# FacturaScripts Plugin - Guía para Desarrolladores

## Contexto del Plugin

Este directorio contiene un **plugin de FacturaScripts**. Los plugins extienden el comportamiento del core sin modificarlo, siguiendo la arquitectura de extensiones de FacturaScripts.

Para las convenciones generales de programación, arquitectura del framework, modelos, controladores y reglas de desarrollo, consulta el CLAUDE.md del core en:

**[../../CLAUDE.md](../../CLAUDE.md)**

---

## Estructura del Plugin

```
MiPlugin/
├── Assets/              # CSS, JS e imágenes propias del plugin
├── Controller/          # Controladores del plugin
├── Data/
│   └── Table/          # XMLs de estructura de tablas adicionales
├── Extension/           # Extensiones de clases del core
│   ├── Controller/     # Extiende controladores del core
│   ├── Model/          # Extiende modelos del core
│   ├── Lib/            # Extiende librerías del core
│   ├── View/           # Extiende vistas XML del core
│   └── Html/           # Extiende vistas HTML del core
├── Html/                # Plantillas Twig del plugin
├── Lib/                 # Librerías propias del plugin
├── Model/               # Modelos del plugin
├── Translation/         # Traducciones en formato JSON
├── XMLView/             # Vistas XML del plugin
├── facturascripts.ini   # Metadatos del plugin (obligatorio)
├── Init.php             # Hooks de inicialización (opcional)
└── Cron.php             # Tareas programadas (opcional)
```

---

## Archivos Clave

### facturascripts.ini
Metadatos del plugin, **obligatorio**:
```ini
name = MiPlugin
version = 1
description = Descripción del plugin
min_version = 2022.1
max_version = 9999
```

### Init.php
Punto de entrada para registrar hooks y ejecutar lógica en la inicialización del plugin. Se ejecuta en cada carga de FacturaScripts.

### Extension/
Permite modificar el comportamiento del core **sin tocar sus archivos**. Es el mecanismo principal para extender modelos, controladores y librerías existentes.

---

## Reglas Fundamentales

- El namespace del plugin es siempre `FacturaScripts\Plugins\NombrePlugin`
- **Nunca** modificar archivos dentro de `Core/` ni `Dinamic/`
- Usar `Extension/` para alterar comportamiento de clases del core
- Las tablas nuevas se definen en `Table/nombre_tabla.xml`
- Las traducciones van en `Translation/` como archivos JSON (`es_ES.json`, `en_EN.json`, etc.)
