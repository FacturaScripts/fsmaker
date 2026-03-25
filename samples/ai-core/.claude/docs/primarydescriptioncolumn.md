# Método primaryDescriptionColumn() del modelo

> **ID:** 629 | **Permalink:** primarydescriptioncolumn-955 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/primarydescriptioncolumn-955

El método **primaryDescriptionColumn()** del modelo devuelve el nombre de la **columna** que se utilizará para describir el registro. Esto puede ser un título, nombre o descripción del mismo.

Para ilustrar su uso, consideremos el modelo [Familia](https://doc.facturascripts.com/classes/FacturaScripts-Core-Model-Familia.html). Este modelo tiene una columna ``codfamilia`` que es la clave primaria de la tabla. Sin embargo, al mostrar la información al usuario, no es relevante que vea que está editando la familia ``1234``; lo que es importante es que vea ``SAMSUNG``, que corresponde al campo ``descripcion`` del modelo. La función **primaryDescriptionColumn()** permite especificar qué columna utilizar como título o descripción.

![ejemplo primaryDescriptionColumn](https://i.imgur.com/YStwmvI.png)

**Por defecto**, la columna utilizada es la misma que la primaria. Por lo tanto, solo es necesario sobrescribir esta función si se desea realizar un cambio.

```php
public function primaryDescriptionColumn(): string
{
	return 'nombre';
}
```

## Véase también
- [$modelo->primaryDescription()](/publicaciones/primarydescription-115)
