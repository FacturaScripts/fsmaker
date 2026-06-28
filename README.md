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
Crea la estructura completa de un nuevo plugin con todas las carpetas necesarias.

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
Genera archivo .gitignore optimizado para plugins de FacturaScripts.

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

#### `fsmaker web`
Inicia una interfaz web local para ejecutar comandos de fsmaker desde el navegador.

```bash
fsmaker web
```

Opciones útiles:

```bash
fsmaker web --port=8788
fsmaker web --host=0.0.0.0 --no-open
FSM_HOST=0.0.0.0 FSM_PORT=8788 fsmaker web
```

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

### Usar interfaz web local
```bash
cd MiPlugin/
fsmaker web
# abre http://127.0.0.1:8787 por defecto, configurable con --host/--port o FSM_HOST/FSM_PORT
# en "Respuestas" escribe una por línea en orden de los prompts
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
