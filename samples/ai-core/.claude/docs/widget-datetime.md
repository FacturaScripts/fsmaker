# Widget Datetime

> **ID:** 1286 | **Permalink:** widget-datetime | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-datetime

En los **archivos XMLView** podemos usar un widget de fecha y hora, o WidgetDatetime, para mostrar o editar fecha y hora a la vez.

```
<column name="creation-date" display="right" order="190">
	<widget type="datetime" fieldname="creationdate"/>
</column>
```

Estos son los atributos disponibles en la etiqueta widget:
- **fieldname**: nombre del campo que contiene la información. **Obligatorio**.
- **required**: impide guardar los datos del formulario si el usuario no ha escrito nada en el campo.
- **readonly**: impide modificar el valor.

Así es cómo se ve el widget datetime en los formularios de edición. Aunque dependiendo del navegador puede variar su aspecto:

![widget datetime](https://i.imgur.com/Oyv8nra.png)

Y así es como se ve el widget datetime en los listados:

![widget datetime](https://i.ibb.co/6J4tJQ9/datetime.png)
