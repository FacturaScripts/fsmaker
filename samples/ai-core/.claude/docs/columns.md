# Columns (XMLView)

> **ID:** 644 | **Permalink:** columns-88 | **Última modificación:** 28-05-2025
> **URL oficial:** https://facturascripts.com/columns-88

En los archivos de la carpeta XMLView tenemos la estructura de campos a mostrar en listados o formularios de edición. Un archivo debe tener una **etiqueta view** y dentro de esta una **etiqueta column**.

Dentro de la **etiqueta column** podemos tener varias [etiquetas column](/publicaciones/column-725), si el archivo es para un listado, o varias [etiquetas group](/publicaciones/group-747), si el archivo es para un formulario de edición. Las etiquetas group o grupos sirven para agrupar varias columnas.

```
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="data" numcolumns="12">
            <column name="id" display="none" order="100">
                <widget type="text" fieldname="id" />
            </column>
            <column name="name" order="110">
                <widget type="text" fieldname="nombre" />
            </column>
            <column name="price" display="right" order="120">
                <widget type="number" fieldname="precio" />
            </column>
        </group>
    </columns>
</view>
```

## Los name son identificadores
Recuerda que el **atributo name** en las etiquetas son usados por FacturaScripts como identificador, así que no podrás tener dos grupos o columnas con el mismo name.
