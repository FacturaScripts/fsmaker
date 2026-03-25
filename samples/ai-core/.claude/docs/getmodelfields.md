# Método getModelFields() del modelo

> **ID:** 634 | **Permalink:** getmodelfields-769 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/getmodelfields-769

El método `getModelFields()` del modelo es una herramienta fundamental en FacturaScripts. **Devuelve un array que contiene las columnas de la tabla junto con sus respectivas propiedades**, lo que resulta especialmente útil cuando no conocemos todos los nombres de columnas del modelo.

## Ejemplo de Uso

A continuación se presenta un ejemplo básico de cómo utilizar `getModelFields()`:

```php
$familia = new Familia();
$fields = $familia->getModelFields();
```

## Valores Devueltos

El método devuelve un array con información detallada sobre cada columna, incluyendo el tipo de dato, si es clave, el valor por defecto, entre otros. A continuación se muestra una representación de ejemplo de lo que se devuelve:

```php
array (size=3)
  'descripcion' => 
    array (size=6)
      'type' => string 'varchar(100)' (length=12)
      'key' => string '' (length=0)
      'default' => null
      'extra' => string '' (length=0)
      'is_nullable' => string 'NO' (length=2)
      'name' => string 'descripcion' (length=11)
  'codfamilia' => 
    array (size=6)
      'type' => string 'varchar(8)' (length=10)
      'key' => string 'PRI' (length=3)
      'default' => null
      'extra' => string '' (length=0)
      'is_nullable' => string 'NO' (length=2)
      'name' => string 'codfamilia' (length=10)
  'madre' => 
    array (size=6)
      'type' => string 'varchar(8)' (length=10)
      'key' => string 'MUL' (length=3)
      'default' => null
      'extra' => string '' (length=0)
      'is_nullable' => string 'YES' (length=3)
      'name' => string 'madre' (length=5)
```

## Nota Adicional
Este método es parte del núcleo de FacturaScripts, ofreciendo una vista detallada y programáticamente accesible de la estructura de las tablas del modelo.
