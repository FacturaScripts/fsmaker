# FacturaScripts - Guía para Desarrolladores

## Resumen del Proyecto

**FacturaScripts** es un software ERP y de contabilidad open source para pequeñas y medianas empresas, construido con PHP moderno (>=8.0) y Bootstrap 5. Permite gestionar facturas, inventario, contabilidad, clientes, proveedores y mucho más mediante una arquitectura extensible basada en plugins.

- **Licencia:** LGPL-3.0-or-later
- **Repositorio:** https://github.com/NeoRazorX/facturascripts
- **Web:** https://facturascripts.com
- **PHP:** >=8.0 | **Template engine:** Twig 3 | **DB:** MySQL / PostgreSQL
- **Discord:** https://discord.gg/qKm7j9AaJT

---

## Estructura del Proyecto

```
facturascripts/
├── Core/                    # Núcleo del framework
│   ├── Assets/             # CSS, JS, Fuentes, Imágenes del core
│   ├── Base/               # Clases base: DataBase, MiniLog, Utils...
│   │   └── DataBase/       # Engines MySQL y PostgreSQL, DataBaseWhere
│   ├── Contract/           # Interfaces del sistema
│   ├── Controller/         # Controladores del core (About, Dashboard, EditXxx, ListXxx...)
│   ├── Data/               # Archivos XML de estructura de tablas
│   ├── DataSrc/            # Fuentes de datos en caché
│   ├── Error/              # Controladores de errores
│   ├── Html/               # Plantillas Twig del core
│   ├── Lib/                # Librerías de negocio
│   │   ├── API/            # API REST
│   │   ├── Accounting/     # Contabilidad
│   │   ├── AjaxForms/      # Formularios Ajax (calculadora de documentos)
│   │   ├── Email/          # Envío de emails (NewMail)
│   │   ├── Export/         # Exportación PDF, Excel
│   │   ├── ExtendedController/  # ListController, EditController, PanelController
│   │   ├── Import/         # Importación de datos
│   │   ├── ListFilter/     # Filtros para ListController
│   │   ├── PDF/            # Generación de PDF
│   │   └── Widget/         # Widgets de XMLView
│   ├── Model/              # Modelos de datos del core
│   │   └── Base/           # Clases base de modelos (ModelClass, BusinessDocument...)
│   ├── Migrations.php      # Sistema de migraciones
│   └── Kernel.php          # Kernel de la aplicación
│
├── Dinamic/                 # Copia dinámica del core (generada automáticamente)
│   ├── Lib/                # Extensiones de librerías
│   └── Model/              # Extensiones de modelos
│
├── Plugins/                 # Plugins instalados
│   └── MiPlugin/           # Estructura de un plugin
│       ├── Assets/         # CSS, JS propios
│       ├── Controller/     # Controladores del plugin
│       ├── Data/           # XMLs de tablas adicionales
│       ├── Extension/      # Extensiones de clases del core
│       ├── Html/           # Plantillas Twig del plugin
│       ├── Lib/            # Librerías del plugin
│       ├── Model/          # Modelos del plugin
│       ├── Translation/    # Traducciones JSON
│       ├── XMLView/        # Vistas XML del plugin
│       ├── facturascripts.ini  # Metadatos del plugin
│       ├── Init.php        # Inicialización y hooks
│       └── Cron.php        # Tareas programadas
│
├── Test/                    # Tests unitarios (PHPUnit)
├── vendor/                  # Dependencias Composer
├── index.php               # Punto de entrada
└── composer.json           # Dependencias del proyecto
```

---

## Arquitectura

### Patrón MVC
FacturaScripts sigue el patrón **MVC** adaptado:
- **Modelos** (`Core/Model/`) — Clases que representan tablas de la base de datos
- **Controladores** (`Core/Controller/`) — Gestionan la lógica de negocio y las peticiones HTTP
- **Vistas** (`Core/Html/` + `XMLView/`) — Plantillas Twig + definiciones XML de vistas

### Sistema Dinamic
La carpeta `Dinamic/` es una **copia generada automáticamente** del core que permite a los plugins extender clases sin modificar el código fuente. Nunca se edita manualmente. Se regenera al instalar/desinstalar plugins.

### Autoloading PSR-4
```
FacturaScripts\Core\     → Core/
FacturaScripts\Dinamic\  → Dinamic/
FacturaScripts\Plugins\  → Plugins/
FacturaScripts\Test\     → Test/
```

### Controladores Extendidos
Los controladores del core proporcionan clases base que simplifican el desarrollo:
- **ListController** — Listados con filtros, búsqueda y paginación
- **EditController** — Formularios de edición de un registro
- **PanelController** — Combinación de vistas (lista + edición)

### Vistas XML (XMLView)
Las vistas se definen mediante archivos XML (`.xml`) que especifican columnas, widgets, filtros y acciones. El sistema genera el HTML automáticamente a partir de esta definición.

### Sistema de Extensiones
Los plugins pueden extender clases del core sin modificarlas usando la carpeta `Extension/`:
- `Extension/Controller/` — Extiende controladores
- `Extension/Model/` — Extiende modelos
- `Extension/Lib/` — Extiende librerías
- `Extension/View/` — Extiende vistas XML
- `Extension/Html/` — Extiende vistas HTML

---

## Reglas de Desarrollo

### Convenciones de Código
- **Estándar:** PSR-12 (verificar con `composer cs-check`, corregir con `composer cs-fix`)
- **Análisis estático:** PHPStan (`composer phpstan`)
- **Tests:** PHPUnit 9 (`composer test`)
- **Namespace:** siempre `FacturaScripts\Plugins\NombrePlugin`

### Reglas de los Modelos
- Heredar de `FacturaScripts\Core\Base\DataBase\ModelClass` (vía Dinamic)
- El método `tableName()` devuelve el nombre de la tabla en plural y minúsculas
- El método `primaryColumn()` devuelve el nombre de la columna primaria
- Definir la estructura de la tabla en `Data/Table/NombreModelo.xml`
- Implementar `test()` para validación antes de guardar

### Reglas de los Controladores
- Heredar del controlador adecuado (`ListController`, `EditController`, `PanelController` o `Controller`)
- Definir `getPageData()` con nombre, icono, menú y permisos
- Los permisos se gestionan automáticamente con el sistema de roles

### Reglas de los Plugins
- El archivo `facturascripts.ini` es obligatorio (nombre, versión, compatibilidad)
- Versionar siguiendo semver
- No modificar nunca archivos del `Core/` ni `Dinamic/`
- Usar extensiones para modificar comportamiento del core
- Los archivos de traducción van en `Translation/` en formato JSON

### Base de Datos
- No usar SQL directo: usar los modelos y `DataBaseWhere`
- Las migraciones de tabla se gestionan automáticamente vía XML
- Soporta MySQL y PostgreSQL

---

## Sistema de Plugins

### Estructura mínima de un plugin
```
MiPlugin/
├── facturascripts.ini    # Obligatorio: metadatos
└── Init.php              # Opcional: hooks de inicialización
```

### facturascripts.ini
```ini
name = MiPlugin
version = 1
description = Descripción del plugin
min_version = 2022.1
max_version = 9999
```

### Publicar en el marketplace
Los plugins se publican en https://facturascripts.com. Deben seguir las guías de calidad y pasar revisión antes de publicarse.

### Prioridad de plugins
Los plugins tienen un sistema de prioridades. Los archivos con el mismo nombre en `Dinamic/` se sobreescriben según la prioridad del plugin (mayor número = mayor prioridad).

---

## Comandos útiles

```bash
# Instalar dependencias
composer install

# Ejecutar tests
composer test

# Verificar estilo de código
composer cs-check

# Corregir estilo de código
composer cs-fix

# Análisis estático
composer phpstan

# Servidor de desarrollo
composer dev-server
```

---

## Documentación para Programadores

La documentación técnica completa está disponible en:
- **`.claude/docs/`** — Documentación oficial descargada de facturascripts.com
- **Online:** https://facturascripts.com/publicaciones (filtrar por "Para desarrolladores")

### Índice de documentación local (`.claude/docs/`)
Los archivos están organizados por temática y corresponden a publicaciones oficiales de FacturaScripts con `fordevelopers=true`.

---

## Skills disponibles

Las skills de desarrollo están en `.claude/skills/` y proporcionan flujos optimizados para tareas comunes:

- **`crear-modelo`** — Crear un nuevo modelo con tabla XML
- **`crear-controlador`** — Crear controladores (List, Edit, Panel)
- **`crear-plugin`** — Scaffold completo de un plugin
- **`crear-extension`** — Crear extensiones del core
- **`crear-widget`** — Añadir widgets en XMLView
- **`crear-xmlview`** — Crear/editar vistas XML

Ver cada skill para instrucciones detalladas de uso.
