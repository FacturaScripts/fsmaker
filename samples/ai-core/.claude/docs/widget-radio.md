# Widget Radio

> **ID:** 1636 | **Permalink:** widget-radio | **Última modificación:** 10-01-2026
> **URL oficial:** https://facturascripts.com/widget-radio

El **Widget Radio** permite a los usuarios elegir entre varias opciones de manera visual. En todo momento, se pueden ver todas las opciones disponibles sin necesidad de desplegar ningún menú.

### ⚙️ Parámetros del Widget

- **fieldname**: Nombre del campo que contiene la información. **Obligatorio**.
- **required**: Impide guardar los datos del formulario si el usuario no ha ingresado nada en el campo.
- **readonly**: Impide modificar el valor del campo.
- **onclick**: URL o controlador al que será redirigido el usuario al hacer clic. A esta URL se le añadirá **?code=** seguido del valor del campo.
- **translate**: Indica si los títulos de los valores a mostrar al usuario deben ser traducidos automáticamente. Establecer en `true` para activar la traducción.

### 🖼️ Valores a Mostrar

Se pueden mostrar **valores fijos** o añadir valores manualmente desde el **controlador**.

#### Valores Fijos

```html
<column name="template" numcolumns="12" order="100">
    <widget type="radio" fieldname="template" required="true">
        <values title="book">book</values>
        <values title="subtract">subtract</values>
        <values title="do-nothing">do-nothing</values>
        <values title="add">add</values>
        <values title="foresee">foresee</values>
    </widget>
</column>
```

*Tambié se puede especificar el campo `class` para que los botones se muestren en línea utilizando: `form-check-inline`.*

![](https://i.imgur.com/i2njCkq.png)

#### Valores con Imágenes

Se pueden mostrar imágenes en lugar de texto para la selección. Para esto, hay que habilitar la opción de imágenes con **images="true"** y especificar la ruta donde se encuentran las imágenes en formato **.png** usando **path="tu-ruta"**.

En el atributo `title` de la etiqueta `<values>` se coloca el nombre del archivo sin la extensión.

```html
<column name="template" numcolumns="12" order="100">
    <widget type="radio" images="true" path="/Dinamic/Assets/Images/PlantillasPDF/" fieldname="template2" required="true">
        <values title="template1">template1.png</values>
        <values title="template2">template2.png</values>
        <values title="template3">template3.png</values>
        <values title="template4">template4.png</values>
    </widget>
</column>
```
