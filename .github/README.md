# GitHub Actions para FSMaker

Este directorio contiene las GitHub Actions configuradas para el proyecto FSMaker.

## Workflows Disponibles

### 1. `tests.yml` - Ejecución de Tests
**Se ejecuta en:** Push y Pull Requests a `main` y `develop`

Ejecuta la suite completa de tests en múltiples versiones de PHP:
- PHP 8.0, 8.1, 8.2, 8.3, 8.4
- Instala dependencias con Composer
- Ejecuta `composer test`
- Genera cobertura de código (solo PHP 8.3)
- Sube reporte de cobertura a Codecov

**Comandos ejecutados:**
```bash
composer validate --strict
composer install --prefer-dist --no-progress --no-suggest
composer run-script test
./vendor/bin/phpunit --coverage-clover build/logs/clover.xml  # Solo PHP 8.3
```

### 2. `quality.yml` - Verificación de Calidad de Código
**Se ejecuta en:** Push y Pull Requests a `main` y `develop`

Verifica la calidad del código:
- Revisa sintaxis PHP en todos los archivos
- Ejecuta validaciones de seguridad
- Verifica estructura del proyecto

**Comandos ejecutados:**
```bash
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
security-checker security:check composer.lock
```

### 3. `release.yml` - Creación de Releases
**Se ejecuta en:** Push de tags `v*`

Automatiza el proceso de release:
- Ejecuta tests completos
- Crea archivo ZIP con la distribución
- Genera release en GitHub
- Sube el archivo ZIP como asset

## Configuración de Scripts Composer

Se han añadido los siguientes scripts al `composer.json`:

```json
{
  "scripts": {
    "test": "phpunit",
    "test-coverage": "phpunit --coverage-html build/coverage"
  }
}
```

### Uso local:
```bash
# Ejecutar tests
composer test

# Generar reporte de cobertura HTML
composer test-coverage
```

## Badges Recomendados

Puedes añadir estos badges al README principal:

```markdown
![Tests](https://github.com/facturascripts/fsmaker/workflows/Tests/badge.svg)
![Code Quality](https://github.com/facturascripts/fsmaker/workflows/Code%20Quality/badge.svg)
[![codecov](https://codecov.io/gh/facturascripts/fsmaker/branch/main/graph/badge.svg)](https://codecov.io/gh/facturascripts/fsmaker)
```

## Configuración Requerida

### Secrets de GitHub (opcional)
- `CODECOV_TOKEN`: Para subir reportes de cobertura a Codecov

### Configuración de Ramas
Los workflows están configurados para ejecutarse en:
- `main`: Rama principal
- `develop`: Rama de desarrollo

## Personalización

### Cambiar versiones de PHP
Edita el array `matrix.php-version` en `tests.yml`:

```yaml
strategy:
  matrix:
    php-version: [8.0, 8.1, 8.2, 8.3, 8.4]
```

### Añadir más checks de calidad
Edita `quality.yml` para añadir herramientas como:
- PHP CS Fixer
- PHPStan
- Psalm
- PHP_CodeSniffer

### Modificar proceso de release
Edita `release.yml` para personalizar qué archivos se incluyen en el ZIP de distribución.

## Monitoreo

### Resultados de Tests
Los resultados de tests están disponibles en:
- GitHub Actions tab
- Pull Request checks
- Codecov dashboard (si está configurado)

### Notificaciones
GitHub enviará notificaciones por:
- Tests fallidos
- Releases creados
- Errores en workflows