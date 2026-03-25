# Widget Checkbox

> **ID:** 659 | **Permalink:** widget-checkbox-35 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-checkbox-35

En los **archivos XMLView** podemos usar un widget checkbox, o WidgetCheckbox, para mostrar o editar valores de verdadero o falso.

```
<column name="email-sent" display="center" order="140">
	<widget type="checkbox" fieldname="femail"/>
</column>
```

Así es cómo se ve el widget checkbox en formularios de edición:

![widget checkbox formulario](https://i.imgur.com/qPomoRs.png)

Y así es cómo se ve el widget checkbox en listados:

![widget checkbox listados](https://i.imgur.com/OonKO4l.png)

## Alineación vertical
Cuando mezcla columnas select, text y checkbox sucede que las checkbox se alinean en la parte de arriba. Puede modificar este comportamiento cambiando la alineación vertical de la [etiqueta group](/publicaciones/group-747) con el **parámetro valign**. Ejemplo:

```
<group name="data" numcolumns="12" valign="bottom">
    <column name="name" order="100">
        <widget type="text" fieldname="nombre" />
    </column>
    <column name="active" order="110">
        <widget type="checkbox" fieldname="activo" />
    </column>
</group>
```
