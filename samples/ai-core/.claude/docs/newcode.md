# Método newCode() del modelo

> **ID:** 628 | **Permalink:** newcode-75 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/newcode-75

La función `$modelo->newCode()` obtiene el siguiente número disponible para el campo específico que se solicite. Esta funcionalidad es útil para evitar la generación de duplicados en las entradas del sistema.

### Uso
Para utilizar esta función, simplemente invoca `$modelo->newCode()` en el contexto adecuado del modelo correspondiente.

### Ejemplo
```php
$nuevoCodigo = $modelo->newCode();
echo 'El siguiente número disponible es: ' . $nuevoCodigo;
```
Este código imprimirá el siguiente número disponible para el modelo que se esté utilizando.
