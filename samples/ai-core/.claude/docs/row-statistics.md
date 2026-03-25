# Row statistics

> **ID:** 647 | **Permalink:** row-statistics-603 | **Última modificación:** 10-03-2026
> **URL oficial:** https://facturascripts.com/row-statistics-603

Este tipo de row permite definir botones o etiquetas que muestran datos calculados en el momento por el controlador. Estas etiquetas se definen con la etiqueta **datalabel** que tiene estos atributos:
- **icon**: [icono de la etiqueta](/publicaciones/iconos-disponibles-308).
- **label**: texto del botón. **Se traducirá automáticamente**.
- **function**: nombre de la función del controlador que se ejecuta para devolver el texto a visualizar.
- **link**: URL destino, donde se redigirá al usuario al hacer click sobre el botón.

### Ejemplo:
```
<rows>
	<row type="statistics">
		<datalabel icon="fa fa-copy" label="Alb. Pdtes:" function="nombre_function" link="#"/>
		<datalabel icon="fa fa-copy" label="Pdte Cobro:" function="nombre_function" link="#"/>
	</row>
</rows>
```
