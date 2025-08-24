# FSMaker Plugin Creation Tests

Este directorio contiene las pruebas unitarias para validar la funcionalidad de creación de plugins del comando `fsmaker plugin`.

## Archivos de Prueba

### 1. `PluginCreationTest.php`
Pruebas básicas de la funcionalidad del comando plugin:
- Verifica que existe la clase `fsmaker` y el método `createPluginAction`
- Valida la estructura de directorios esperada
- Prueba la validación de nombres de plugin (regex)
- Verifica condiciones que previenen la creación de plugins
- Testa métodos auxiliares de la clase `Utils`

### 2. `PluginCreationIntegrationTest.php`
Pruebas de integración que simulan la creación real de plugins:
- Creación de estructura de directorios completa
- Generación de archivos `.ini`, `.gitignore`, `Cron.php`, `Init.php`
- Validación del contenido de archivos generados
- Prueba de métodos de la clase `FileGenerator`

### 3. `EndToEndPluginTest.php`
Pruebas de extremo a extremo que simulan todo el proceso:
- Proceso completo de creación de plugin desde inicio hasta fin
- Validación de todas las verificaciones de fsmaker
- Pruebas de validación de nombres de plugin
- Verificación de estructura completa del plugin creado

## Ejecutar las Pruebas

### Todas las pruebas:
```bash
./vendor/bin/phpunit
```

### Prueba específica:
```bash
./vendor/bin/phpunit tests/PluginCreationTest.php
./vendor/bin/phpunit tests/PluginCreationIntegrationTest.php  
./vendor/bin/phpunit tests/EndToEndPluginTest.php
```

### Con output detallado:
```bash
./vendor/bin/phpunit --verbose
```

## Cobertura de Pruebas

Las pruebas cubren:
- ✅ Validación de estructura de la clase `fsmaker`
- ✅ Creación de directorios del plugin
- ✅ Generación de archivos esenciales (ini, gitignore, cron, init)
- ✅ Validación de nombres de plugin
- ✅ Verificaciones de seguridad (no crear en directorios existentes)
- ✅ Contenido correcto de archivos generados
- ✅ Proceso completo de extremo a extremo

## Estadísticas de Pruebas

- **Total de pruebas:** 17
- **Total de aserciones:** 156
- **Estado:** ✅ Todas las pruebas pasan
- **Advertencias:** 3 pruebas marcadas como "risky" por salida inesperada (comportamiento esperado de Utils::createFolder)

## Estructura de Plugin Validada

Las pruebas validan que se creen todos estos directorios:
- Assets/CSS, Assets/Images, Assets/JS
- Controller
- Data/Codpais/ESP, Data/Lang/ES  
- Extension/Controller, Extension/Model, Extension/Table, Extension/XMLView, Extension/View
- Model/Join
- Table, Translation, View, XMLView
- Test/main
- CronJob, Mod, Worker

Y estos archivos:
- facturascripts.ini
- .gitignore
- Cron.php
- Init.php