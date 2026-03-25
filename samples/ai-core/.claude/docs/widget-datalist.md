# Widget Datalist

> **ID:** 1064 | **Permalink:** widget-datalist | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-datalist

El **Widget Datalist**, también conocido como **WidgetList**, es una especialización del [WidgetSelect](https://facturascripts.com/publicaciones/widget-select-557) que permite mostrar valores relacionados con otras tablas (o incluso con la misma tabla) en función del texto introducido por el usuario. A diferencia de mostrar la lista completa de valores, este widget presenta una lista de posibles coincidencias conforme el usuario escribe.

## Ejemplo Básico

Un ejemplo sencillo es un selector de ciudad en el menú **Administrador > Empresas** al elegir una empresa:

```xml
<column name="city" numcolumns="4" order="130">
	<widget type="datalist" fieldname="ciudad">
		<values source="ciudades" fieldcode="ciudad" fieldtitle="ciudad" limit="9000"/>
	</widget>
</column>
```

![Widget Datalist en el campo Ciudad](https://i.imgur.com/oCU4ONH.gif)

## Ejemplo de Selectores Anidados

El **datalist** también es útil para crear selectores anidados. Por ejemplo, para que las provincias se carguen una vez que se selecciona un país, el código del datalist sería:

```xml
<column name="province" numcolumns="4" order="140">
	<widget type="datalist" fieldname="provincia" parent="codpais">
		<values source="provincias" fieldcode="provincia" fieldtitle="provincia" fieldfilter="codpais"/>
	</widget>
</column>
```

En este ejemplo, utilizamos las propiedades **parent** y **fieldfilter**.

## Propiedades del Widget

Las propiedades disponibles en la etiqueta del widget son:

- **fieldname**: Nombre del campo que contiene la información. **Obligatorio**.
- **required**: Impide guardar los datos del formulario si el usuario no ha escrito nada en el campo.
- **readonly**: Impide modificar el valor.
- **onclick**: URL o controlador al que será redirigido el usuario al hacer clic, añadiendo **?code=** y el valor del campo a esta URL.
- **icon**: [Icono a mostrar en el campo de edición](https://facturascripts.com/publicaciones/iconos-disponibles-308).
- **translate**: Establecer en true para traducir automáticamente los títulos de los valores a mostrar al usuario.
- **parent**: Indica el **fieldname** del widget si el datalist depende de otro widget datalist o select.
- **fieldfilter**: Campo del datalist o select que se utiliza para filtrar la información del datalist actual.

## Definición de la Clase

Consulta la lista completa de propiedades y métodos del widget select en la [documentación de la clase WidgetDatalist](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetDatalist.html).

## Opciones de Coloreado

Recuerda que [todos los widgets tienen una serie de propiedades y opciones comunes](https://facturascripts.com/publicaciones/widget-238).

## Valores a Mostrar

Podemos mostrar **valores de una tabla** concreta, **valores fijos** o incluso añadir valores manualmente desde el **controlador**.

### Valores de una Tabla

```xml
<widget type="datalist" fieldname="codpais" required="true">
	<values source="paises" fieldcode="codpais" fieldtitle="nombre"/>
</widget>
```

- **source**: Nombre de la tabla a consultar. También se puede usar el nombre de un modelo, por ejemplo, **Proveedor**.
- **fieldcode**: Columna de la tabla para el valor seleccionado. Esta columna es opcional si en **source** se ha escrito el nombre de un modelo.
- **fieldtitle**: Columna de la tabla para el texto a mostrar al usuario. Si no se indica, se utiliza **fieldcode**. Esta columna es opcional en el caso de modelos.
	- Si se ha indicado **translate** en el widget, este texto será traducido.

### Valores Fijos

```xml
<widget type="datalist" fieldname="actualizastock" translate="true" required="true">
	<values title="book">-2</values>
	<values title="subtract">-1</values>
	<values title="do-nothing">0</values>
	<values title="add">1</values>
	<values title="foresee">2</values>
</widget>
```

### Añadir Valores desde el Controlador

Para cargar una lista específica de valores en un widget datalist, se puede usar el método [setValuesFromArray()](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetDatalist.html#method_setValuesFromArray):

```php
$column = $this->views[VIEW_NAME]->columnForName(NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW);
if($column && $column->widget->getType() === 'datalist') {
	$customValues = [
		['value' => '1', 'title' => 'UNO'],
		['value' => '2', 'title' => 'DOS'],
		['value' => '3', 'title' => 'TRES'],
		['value' => '14', 'title' => 'CATORCE'],
	];
	$column->widget->setValuesFromArray($customValues);
	
	// Para incluir un valor null, usa la siguiente línea:
	// $column->widget->setValuesFromArray($customValues, false, true);
}
```

Recuerda sustituir **VIEW_NAME** por el nombre de la vista/pestaña correspondiente, y **NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW** por el nombre de la columna que contiene el widget en el archivo xmlview.

### Con CodeModel

También es posible utilizar la clase **CodeModel** para obtener valores y cargarlos en el widget. Por ejemplo, para cargar una lista de clientes y sus números de teléfono, se utilizará el método [setValuesFromCodeModel()](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Widget-WidgetDatalist.html#method_setValuesFromCodeModel):

```php
$column = $this->views[VIEW_NAME]->columnForName(NAME_DE_LA_COLUMNA_EN_EL_XMLVIEW);
if($column && $column->widget->getType() === 'datalist') {
	$customValues = $this->codeModel->all('clientes', 'codcliente', 'telefono1');
	$column->widget->setValuesFromCodeModel($customValues);
}
```
