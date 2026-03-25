# Widget Select

> **ID:** 660 | **Permalink:** widget-select-557 | **Última modificación:** 03-01-2026
> **URL oficial:** https://facturascripts.com/widget-select-557

El widget select, o WidgetSelect, permite mostrar valores que están relacionados con otras tablas (*o con la misma*). Un ejemplo muy sencillo es un selector de país.

```
<column name="country" numcolumns="2" order="150">
	<widget type="select" fieldname="codpais" required="true">
		<values source="paises" fieldcode="codpais" fieldtitle="nombre"/>
	</widget>
</column>
```

- **fieldname**: nombre del campo que contiene la información. **Obligatorio**.
- **required**: impide guardar los datos del formulario si el usuario no ha escrito nada en el campo.
- **readonly**: impide modificar el valor.
- **onclick**: URL o controlador al que será redirigido el usuario al hacer clic. A esta URL se le añade **?code=** y el valor del campo.
- **icon**: [icono a mostrar en el campo de edición](/publicaciones/iconos-disponibles-308).
- **translate**: true para indicar que se traduzcan automáticamentelos títulos de los valores a mostrar al usuario.
- **limit**: por defecto hay un máximo del 1000 elementos en el selector, pero se puede especificar un límite distinto.

**Definición de la clase**: puede ver la lista completa de propiedades y métodos del widget select en la [documentación de la clase WidgetSelect](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetSelect.html).

🎨 **Opciones de coloreado**: recuerde que [todos los widgets tienen una serie de propiedades y opciones comunes](/publicaciones/widget-238).

Así es como se ve el widget select en los formularios de edición:

![widget select edit](https://i.imgur.com/21Ml5vi.png)

Y así es como se ve en los listados:

![widget select list](https://i.imgur.com/0D7zNya.png)

## 🔠 Valores a mostrar
Podemos mostrar los **valores de una tabla** concreta, **valores fijos** o incluso podemos añadir valores manualmente desde el **controlador**.

### Valores fijos
```
<widget type="select" fieldname="actualizastock" translate="true" required="true">
	<values title="book">-2</values>
	<values title="subtract">-1</values>
	<values title="do-nothing">0</values>
	<values title="add">1</values>
	<values title="foresee">2</values>
</widget>
```

### Valores de una tabla
```
<widget type="select" fieldname="codpais" required="true">
	<values source="paises" fieldcode="codpais" fieldtitle="nombre"/>
</widget>
```

- **source**: nombre de la tabla a consultar. También podemos poner el nombre de un modelo, por ejemplo Proveedor.
- **fieldcode**: columna de la tabla para el valor seleccionado. Si en source se ha escrito el nombre de un modelo, esta columna es opcional.
- **fieldtitle**: columna de la tabla para el texto a mostrar al usuario.
	- Si no se indica fieldtitle, se usa fieldcode.
	- Si en source se ha escrito el nombre de un modelo, esta columna es opcional (es necesario sobreescribir la funcion [primaryDescriptionColumn()](https://facturascripts.com/publicaciones/primarydescriptioncolumn-955) del modelo o se usa fieldcode).
	- Si se ha indicado translate en el widget, este texto se traducirá.

#### 🔍 Filtrar valores
Si queremos mostrar solamente algunos valores de una tabla, por ejemplo todas las provincias del país seleccionado, podemos usar los parámetros **parent** y **fieldfilter**:

```
<widget type="select" fieldname="codpais" required="true">
	<values source="paises" fieldcode="codpais" fieldtitle="nombre"/>
</widget>
<widget type="select" fieldname="provincia" parent="codpais" required="true">
	<values source="provincias" fieldcode="provincia" fieldtitle="nombre" fieldfilter="codpais"/>
</widget>
```

En el segundo select, el de provincias, estamos mostrando solamente aquellas que coinciden con el país seleccionado.

### ⚙️ Añadir valores desde el controlador
Si necesitamos cargar una lista muy concreta de valores en un widget select, podemos usar el método [setValuesFromArray()](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetSelect.html#method_setValuesFromArray):

```
$column = $this->tab(VIEW_NAME)->columnForName(NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW);
if($column && $column->widget->getType() === 'select') {
	$customValues = [
		['value' => '1', 'title' => 'UNO'],
		['value' => '2', 'title' => 'DOS'],
		['value' => '3', 'title' => 'TRES'],
		['value' => '14', 'title' => 'CATORCE'],
	];
	$column->widget->setValuesFromArray($customValues);
	
	// si entre los valores quieres que esté null, mejor ejecuta eso
	//$column->widget->setValuesFromArray($customValues, false, true);
}
```

**¿El widget está en un modal?** Si el select está en un modal, entonces hay que reemplazar la primera línea por esta:

```
$column = $this->tab(VIEW_NAME)->columnModalForName(NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW);
```

Sustituir VIEW_NAME por el nombre de la vista/pestaña que sea, y NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW por el name de la columna que contiene el widget en el archivo xmlview.

### Con CodeModel
También podemos usar la clase **CodeModel** para obtener los valores y cargarlos en el widget. Por ejemplo, vamos a cargar una lista con los clientes y su número de teléfono, y entonces usar el método [setValuesFromCodeModel()](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetSelect.html#method_setValuesFromCodeModel):

```
$column = $this->tab(VIEW_NAME)->columnForName(NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW);
if($column && $column->widget->getType() === 'select') {
	$customValues = $this->codeModel->all('clientes', 'codcliente', 'telefono1');
	$column->widget->setValuesFromCodeModel($customValues);
}
```

### onChange desde javascript
Si quieres ejecutar código javascript cuando haya cambios en el selector, debes añadirlo así:

```
$('select[name="NOMBRE_SELECTOR"]').on('change', function () {
	// tu código aquí
});
```
