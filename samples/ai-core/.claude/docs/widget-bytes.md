# Widget Bytes

> **ID:** 1629 | **Permalink:** widget-bytes | **Última modificación:** 06-02-2026
> **URL oficial:** https://facturascripts.com/widget-bytes

El widget Bytes sirve para mostrar tamaños de archivos o carpetas de forma legible (KB, MB, GB, TB). Toma valores enteros y los convierte automáticamente con la función `Tools::bytes()`

## 🧾 ¿Qué hace?

Muestra un número entero representando un tamaño en bytes (o en otra unidad si usas "multiplier") y lo formatea en la unidad más adecuada (KB, MB, GB, TB) para facilitar su lectura.

## ⚙️ Uso básico

Inserta el widget en una columna de XMLView así:

```xml
<column name="size" display="right" order="150">
    <widget type="bytes" fieldname="file_size"/>
</column>
```

- fieldname: nombre del campo que contiene el valor.
- display="right": alinea el contenido a la derecha en el listado (recomendado para números).

## 🛠️ Parámetro multiplier

Si el valor almacenado no está en bytes, puedes usar el parámetro multiplier para indicar el factor por el que debe multiplicarse el valor antes de convertirlo. Por ejemplo, si tu campo guarda megabytes (MB) y quieres que el widget los muestre formateados, usa multiplier con el número de bytes que hay en 1 MB (1024 * 1024 = 1048576):

```xml
<column name="size" display="right" order="150">
    <widget type="bytes" fieldname="file_size" multiplier="1048576"/>
</column>
```

Ejemplos de multiplicadores frecuentes:

- KB (kibibyte): 1024
- MB (mebibyte): 1048576 (1024^2)
- GB (gibibyte): 1073741824 (1024^3)
- TB (tebibyte): 1099511627776 (1024^4)

Nota: el multiplier debe ser el número de bytes que representa una unidad de la magnitud en la que se guarda tu dato.

## 🔢 Ejemplos de entrada y salida

- Si el campo file_size = 2048 (bytes) y no usas multiplier → se mostrará "2 KB".
- Si el campo file_size = 5 y usas multiplier="1048576" (el campo guarda megabytes) → se mostrará "5 MB".
- Si el campo file_size = 1536 (bytes) → se mostrará "1.5 KB" (según redondeo y formato locales de Tools::bytes()).

## ℹ️ Comportamiento en la interfaz

- En formularios de edición se representa como un widget de tipo number (permitiendo introducir el valor bruto).
- En listados se muestra formateado en la unidad más adecuada (por ejemplo: "1.2 MB").

Puedes usar display="right" en la etiqueta <column> para alinear correctamente valores numéricos en los listados.

![widget bytes](https://i.imgur.com/wWz00eZ.png)

## 🔍 Detalle técnico

Internamente este widget llama a `Tools::bytes()` para formatear el número en la unidad adecuada. Si necesitas un comportamiento distinto, puedes sobrecargar ese método o preprocesar el valor en la consulta/agregación antes de pasarlo al widget.
