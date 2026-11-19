# fsmaker

[![Latest Stable Version](https://poser.pugx.org/facturascripts/fsmaker/v/stable)](https://packagist.org/packages/facturascripts/fsmaker)
[![Total Downloads](https://poser.pugx.org/facturascripts/fsmaker/downloads)](https://packagist.org/packages/facturascripts/fsmaker)
[![License](https://poser.pugx.org/facturascripts/fsmaker/license)](https://packagist.org/packages/facturascripts/fsmaker)
[![PHP Version Require](https://poser.pugx.org/facturascripts/fsmaker/require/php)](https://packagist.org/packages/facturascripts/fsmaker)

Herramienta de creación y actualización de plugins para FacturaScripts.

- 🌐 **Web oficial**: https://facturascripts.com/fsmaker

## 📥 Instalación

### Instalar con composer
Si ya tiene instalado PHP y Composer, puede instalar fsmaker globalmente:

```bash
composer global require facturascripts/fsmaker
```

### Ejecutar
Una vez instalado, fsmaker utiliza **Symfony Console** para una experiencia mejorada:

```bash
# Ver todos los comandos disponibles
fsmaker list

# Ver ayuda de un comando específico
fsmaker controller --help

# Ejecutar un comando
fsmaker model
```

### Comando corto (Linux / Mac)
Para ejecutar fsmaker directamente sin composer:

```bash
sudo ln -s ~/.config/composer/vendor/bin/fsmaker /usr/local/bin/fsmaker
```

Ahora puede usar simplemente:

```bash
fsmaker list
```

## 🚀 Arquitectura moderna con Symfony Console

A partir de la versión 2.0, fsmaker utiliza **Symfony Console** para ofrecer:

- ✅ Sistema de ayuda completo (`fsmaker --help`, `fsmaker zip --help`)
- ✅ Autocompletado de comandos en shell
- ✅ Output coloreado y formateado
- ✅ Control de verbosidad (`-v`, `-vv`, `-vvv`)
- ✅ Mejor organización y mantenibilidad del código
- ✅ Estándar de la industria (Symfony Console)

## ⚡ Comandos disponibles

### 🛠️ Creación de plugins y componentes

#### `fsmaker plugin`
Crea la estructura completa de un nuevo plugin con todas las carpetas necesarias, incluida la estructura de
documentación (ver `fsmaker docs`).

#### `fsmaker model`
Crea un modelo con su tabla XML correspondiente. Opcionalmente puede generar EditController y ListController.

#### `fsmaker controller`
Crea diferentes tipos de controladores:
- Controller básico
- ListController para listados
- EditController para edición

#### `fsmaker extension`
Crea extensiones para:
- Tablas (XML)
- Modelos
- Controladores
- XMLViews
- Vistas HTML

#### `fsmaker test`
Genera archivos de test PHPUnit para el plugin.

#### `fsmaker worker`
Crea workers para el sistema de colas de trabajo.

#### `fsmaker cron`
Crea el archivo principal Cron.php del plugin.

#### `fsmaker cronjob`
Crea tareas programadas individuales.

### 🤖 Generación automática

#### `fsmaker api`
Genera automáticamente la API REST para los modelos del plugin.

#### `fsmaker github-action`
Crea archivo de GitHub Actions para CI/CD.

#### `fsmaker gitignore`
Genera archivo .gitignore optimizado para plugins de FacturaScripts. Ignora lo local de Claude Code y Codex
(`/.claude/*`, `/.agents/*`, `/CLAUDE.local.md`, `/AGENTS.override.md`), pero no `AGENTS.md`, `CLAUDE.md` ni las
skills de `.claude/skills` y `.agents/skills`.

#### `fsmaker docs`
Crea o completa, sin sobrescribir lo que ya exista, la estructura de documentación de un plugin o del core:
- `Doc/User/`: páginas `.md` de uso para usuarios.
- `Doc/Dev/`: páginas `.md` para programadores que personalizan el plugin desde otro plugin.
- `Doc/Context/`: el porqué de las decisiones del código, con su índice en `README.md`.
- `Doc/Assets/`: imágenes, vídeos y otros archivos enlazados desde la documentación.
- `Doc/forja.json`: datos de cada página para publicarlas en la forja de facturascripts.com.
- `AGENTS.md` (descripción breve y enlace a `Doc/`) y `CLAUDE.md` (solo importa `@AGENTS.md`).

Las carpetas vacías llevan un `.gitkeep` para que git las suba.

`Doc/forja.json` agrupa en `User` y `Dev` las páginas de `Doc/User/` y `Doc/Dev/` por su nombre de archivo. Se crea
con las dos secciones vacías; todos los campos de cada página son opcionales:

```json
{
    "User": {
        "mi-pagina-de-prueba.md": {
            "title": "Mi página de prueba",
            "title_short": "Página de prueba",
            "permalink": "mi-pagina-de-prueba",
            "parent": "mi-segunda-pagina-de-prueba.md",
            "ordernum": 100,
            "youtubeid": "https://mi-video-de-youtube.com",
            "imageurl": "Doc/Assets/mi-imagen.png",
            "icon": "fa-solid fa-book",
            "color": "info",
            "keywords": "ventas,facturas",
            "controllers": "ListFacturaCliente,EditFacturaCliente",
            "visibility": true
        }
    },
    "Dev": {}
}
```

| Campo | Qué indica |
|---|---|
| `title` | Título de la página; si falta, se usa el nombre del archivo `.md` formateado |
| `title_short` | Título corto para SEO; si falta, se usa el nombre del archivo `.md` formateado |
| `permalink` | Enlace de la página en la web (máx. 200 caracteres). Si ya existe, la forja le añade algo para hacerlo único al crear la página; después no se puede cambiar |
| `parent` | Página padre, por su nombre de archivo en la misma sección |
| `ordernum` | Orden de la página (número entero) |
| `youtubeid` | Vídeo de YouTube de la página |
| `imageurl` | Imagen principal: una URL o un archivo de `Doc/Assets/` |
| `icon` | Icono de Font Awesome |
| `color` | Color de Bootstrap: `primary`, `secondary`, `success`, `danger`, `warning`, `info` o `dark` |
| `keywords` | Palabras clave para buscar la página, separadas por comas (máx. 300 caracteres) |
| `controllers` | Controladores donde se muestra la página, separados por comas (máx. 300 caracteres) |
| `visibility` | Si la página es visible (`true` o `false`) |

#### `fsmaker init`
Crea el archivo Init.php principal del plugin.

### 🔄 Actualización y migración

#### `fsmaker upgrade`
Actualiza el código PHP, Twig y XML del plugin a las últimas versiones:
- Migra `ToolBox` a `Tools`
- Actualiza namespaces (`Core\Base` → `Core\Lib`)
- Convierte iconos FontAwesome (`fas` → `fa-solid`)
- Actualiza imports de Symfony HttpFoundation
- Añade tipos de retorno (`function clear(): void`)

#### `fsmaker upgrade-bs5`
Migra código de Bootstrap 4 a Bootstrap 5:
- `btn-block` → `w-100`
- `ml-*`/`mr-*` → `ms-*`/`me-*`
- `no-gutters` → `g-0`
- `form-group` → `mb-3`
- `data-toggle` → `data-bs-toggle`
- `badge-*` → `bg-*`
- Y muchos más patrones de migración

### 🔧 Utilidades

#### `fsmaker translations`
Descarga y actualiza las traducciones del plugin.

#### `fsmaker run-tests [ruta]`
Ejecuta los tests del plugin. Opcionalmente especifica la ruta de FacturaScripts.

#### `fsmaker zip`
Genera un archivo ZIP del plugin listo para distribución.

## ✅ Requisitos

- PHP 8.1 o superior
- Composer
- Estar en la carpeta raíz del plugin o core de FacturaScripts

## 📁 Estructura de directorios

fsmaker espera encontrarse en:
- **Plugin**: Carpeta raíz del plugin (contiene `facturascripts.ini`)
- **Core**: Carpeta raíz del core de FacturaScripts

### Estructura típica de plugin generado:
```
MiPlugin/
├── facturascripts.ini
├── Init.php
├── Cron.php
├── Controller/
├── Model/
├── View/
├── XMLView/
├── Table/
├── Extension/
│   ├── Controller/
│   ├── Model/
│   ├── Table/
│   ├── XMLView/
│   └── View/
├── Assets/
│   ├── CSS/
│   ├── JS/
│   └── Images/
├── Data/
│   ├── Codpais/ESP/
│   └── Lang/ES/
├── Test/main/
├── CronJob/
├── Worker/
└── Translation/
```

## 💡 Ejemplos de uso

### Ver todos los comandos disponibles
```bash
fsmaker list
```

### Ver ayuda de un comando
```bash
fsmaker controller --help
fsmaker model --help
```

### Crear un plugin completo
```bash
cd /ruta/desarrollo/
fsmaker plugin
# Introduce: MiPlugin
```

### Crear un modelo con controladores
```bash
cd MiPlugin/
fsmaker model
# Introduce: Cliente (modelo)
# Introduce: clientes (tabla)
# Configura campos con prompts interactivos
# ¿Crear EditController? Si
# ¿Crear ListController? Si
```

### Actualizar código a nuevas versiones
```bash
cd MiPlugin/
fsmaker upgrade        # Migra código PHP/Twig/XML
fsmaker upgrade-bs5    # Migra Bootstrap 4 → 5
```

### Preparar para distribución
```bash
cd MiPlugin/
fsmaker zip
```

### Ejecutar con mayor verbosidad
```bash
fsmaker model -v      # Verbose
fsmaker model -vv     # Very verbose
fsmaker model -vvv    # Debug
```

## 📞 Issues / Feedback

- 💬 **Contacto**: https://facturascripts.com/contacto
- 🐛 **GitHub**: https://github.com/facturascripts/fsmaker