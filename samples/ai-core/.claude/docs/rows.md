# Rows (XMLView)

> **ID:** 645 | **Permalink:** rows-304 | **Última modificación:** 28-05-2025
> **URL oficial:** https://facturascripts.com/rows-304

Dentro de la etiqueta rows de los **archivos XMLView** va la configuración u opciones especiales de la interfaz, como por ejemplo los colores a aplicar a las filas, botones a añadir a la interfaz, etc. Estas opciones las definimos mediante **etiquetas row**.

```
<rows>
	<row type="status">
		<option color="info" fieldname="importe">0</option>
		<option color="success" fieldname="importe">gt:1000</option>
		<option color="warning" fieldname="editable">1</option>
	</row>
	<row type="actions">
		<button type="action" label="renumber" color="warning" action="renumber" icon="fas fa-sort-numeric-down"/>
	</row>
</rows>
```

## Type único
No podemos incluir dos veces el mismo tipo de row, es decir, si ya tienes un row type="status", no puedes añadir otro.
