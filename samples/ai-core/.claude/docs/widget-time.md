# Widget Time

> **ID:** 1188 | **Permalink:** widget-time | **Última modificación:** 06-02-2026
> **URL oficial:** https://facturascripts.com/widget-time

En los **archivos XMLView** podemos utilizar un widget de tipo `time`, también conocido como **WidgetTime**, para mostrar o editar horas.

```xml
<column name="hora" display="right" order="130">
	<widget type="time" fieldname="hora"/>
</column>
```

### Atributos del Widget
Los siguientes atributos están disponibles en la etiqueta del widget:
- **fieldname**: Nombre del campo que contiene la información. **Obligatorio**.
- **required**: Impide guardar los datos del formulario si el usuario no ha ingresado información en el campo.
- **readonly**: Evita que se modifique el valor del campo.
- **icon**: [Icono a mostrar en el campo de edición](https://facturascripts.com/publicaciones/iconos-disponibles-308).
- **step**: Un valor superior igual a 60 eliminará la necesidad de introducir los segundos en los formularios, y  ocultará los segundos en el formulario de edición y en los listados.

### Visualización del Widget
A continuación, se muestra cómo se visualiza el widget `time` en los formularios de edición, aunque su apariencia puede variar dependiendo del navegador:

![Widget time en formularios](https://imgur.com/yiWr1dj.png)

Y así es como se presenta el widget `time` en los listados:

![Widget time en listados](https://imgur.com/uySjBOa.png)

### 🎨 Opciones de Coloado
Recuerda que [todos los widgets tienen una serie de propiedades y opciones comunes](https://facturascripts.com/publicaciones/widget-238).
