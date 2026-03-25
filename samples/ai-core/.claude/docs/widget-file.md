# Widget de Archivos (WidgetFile)

> **ID:** 663 | **Permalink:** widget-file-359 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-file-359

El **Widget de Archivos** (WidgetFile) permite mostrar y adjuntar archivos en los formularios estándar de FacturaScripts.

## Ejemplo de Implementación
A continuación se presenta un ejemplo de cómo implementar el widget de archivos en un formulario:

```xml
<column name="full-path" order="110">
	<widget type="file" fieldname="path"/>
</column>
```

Así es como se ve el widget de archivos en un formulario de edición. El aspecto puede variar dependiendo del navegador:

![Widget de Archivos en Edición](https://i.imgur.com/mdsp1Yz.png)

## Funcionamiento
Este widget permite al usuario adjuntar un archivo que se filtra previamente para evitar la carga de código PHP. Finalmente, **el archivo se mueve a la carpeta `MyFiles`** de FacturaScripts, y el valor almacenado en el modelo es el nombre del archivo. Es responsabilidad del programador mover el archivo a la ubicación deseada.

### Filtrar Tipos de Archivos
Es posible filtrar el tipo de archivos que el campo aceptará utilizando el parámetro **accept**. En el siguiente ejemplo, solamente se admiten archivos con extensiones `.xlsx` o `.xls`:

```xml
<column name="file" numcolumns="12">
   <widget type="file" fieldname="file" accept=".xlsx,.xls"/>
</column>
```

### Permitir Múltiples Archivos
Si se desea permitir la selección de múltiples archivos, se puede añadir el parámetro `multiple` y establecerlo en `true`:

```xml
<column name="file" numcolumns="12">
   <widget type="file" fieldname="file" accept=".xlsx,.xls" multiple="true"/>
</column>
```
