# Método exists() del modelo

> **ID:** 625 | **Permalink:** exists-139 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/exists-139

El método **exists()** del modelo **devuelve true** si el registro correspondiente se encuentra en la **base de datos**. A continuación se muestra un ejemplo:

```
$familia = new Familia();
$familia->codfamilia = 'test4';
$familia->descripcion = 'test4';
if ($familia->exists()) {
    // 'test4' está en la tabla familias
} else {
    // 'test4' NO está en la tabla familias
}
```

## ⚠️ Clave primaria
Es importante tener en cuenta que la comprobación se realiza en base a la [clave primaria del modelo](https://facturascripts.com/publicaciones/primarycolumn-492). Por ejemplo, considere el siguiente caso:

```
$producto = new Producto();
$producto->referencia = '1234';
$existe = $producto->exists();
```

Puede parecer erróneo pensar que `$existe` será `true` si existe un producto con la referencia `1234`. Sin embargo, esto no es correcto. **La clave primaria del modelo Producto es `idproducto`**, por lo que, aunque se especifique una referencia válida, si no se ha asignado un `idproducto`, el método **exists()** devolverá `false`.

Puedes comprobar el [diagrama de clases](https://facturascripts.com/publicaciones/diagramas-de-tablas) para ver todas las clases que tiene FacturaScripts.
