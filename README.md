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
Una vez instalado puede ejecutarlo desde cualquier directorio:

```bash
composer global exec fsmaker
```

### Comando corto (Linux / Mac)
Para ejecutar fsmaker directamente sin composer:

```bash
sudo ln -s ~/.config/composer/vendor/bin/fsmaker /usr/local/bin/fsmaker
```

Ahora puede usar simplemente:

```bash
fsmaker
```

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
# Configura campos
# ¿Crear EditController? 1
# ¿Crear ListController? 1
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

## 📞 Issues / Feedback

- 💬 **Contacto**: https://facturascripts.com/contacto
- 🐛 **GitHub**: https://github.com/facturascripts/fsmaker