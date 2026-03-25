# Group (XMLView)

> **ID:** 666 | **Permalink:** group-747 | **Última modificación:** 18-03-2025
> **URL oficial:** https://facturascripts.com/group-747

La **etiqueta group** sirve para agrupar columnas a mostrar, especialmente en formularios donde muchas veces necesitamos agrupar determinados campos. Los grupos deben ir dentro de la etiqueta ``columns`` y no puede haber grupos dentro de grupos.

## Las 12 columnas
FacturaScripts utiliza bootstrap para el diseño de interfaces. Este framework CSS, como muchos otros, divide el espacio horizontal en 12 columnas. Así no tiene que preocuparse de si el campo debe medir 100px o 150px, solamente debe decidir cuantas de las 12 columnas debe ocupar su campo. Es importante que tenga esto en cuenta si desea ajustar el tamaño de un grupo o una columna. De lo contrario FacturaScripts lo ajustará de forma automática.

## Propiedades de group
Se puede personalizar el grupo mediante los siguientes atributos:
- **name**: Identificador interno del grupo. Es obligatorio su uso. Como norma se recomienda el uso de identificadores en minúsculas y en inglés.
- **title**: (opcional) título a mostrar. **Se traducirá automáticamente**.
- **icon**: [icono a mostrar junto al título](/publicaciones/iconos-disponibles-308). El icono de el grupo sólo se mostrará si hay un título definido.
- **numcolumns**: número de columnas que ocupa el grupo. Por defecto 12.
- **valign**: (opcional) para alinear verticalmente los elementos. Opciones: top, center, bottom.

### Ejemplo:
```
<group name="data" numcolumns="8" title="Identificación internacional" icon="fa-solid fa-globe">
	<column name="code" numcolumns="4" order="100">
		<widget type="text" fieldname="codigo"/>
	</column>
	<column name="description" numcolumns="8" order="110">
		<widget type="text" fieldname="descripcion"/>
	</column>
</group>
```
