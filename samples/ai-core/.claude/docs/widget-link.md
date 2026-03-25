# Widget Link

> **ID:** 664 | **Permalink:** widget-link-84 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-link-84

El widget **Link** se puede utilizar dentro de una columna de un **archivo XMLView** para añadir un enlace que nos llevará a la URL especificada en el campo `fieldname`.

```xml
<column name="web" order="130">
	<widget type="link" fieldname="web"/>
</column>
```

## Visualización en formularios de edición

En los formularios de edición, la etiqueta del campo se convierte en un enlace que apunta al contenido del campo `fieldname` del modelo. En este ejemplo, el campo es `web`.

![widget link edit](https://i.imgur.com/lBXbKxd.png)

## Visualización en listados

En las listados, el widget se muestra de la siguiente manera:

![widget link list](https://i.imgur.com/ZCKXg9n.png)

## Funcionamiento

Al mostrar este widget en pantalla, se generará un enlace a la URL especificada en el campo correspondiente de la base de datos (`fieldname`). El enlace se abrirá en una nueva ventana o pestaña.

### Diferencias con el atributo onclick

La diferencia entre el widget Link y el atributo `onclick` de otros widgets es la siguiente:

- Con el widget Link, el enlace apunta directamente al contenido del campo; si el campo es `web`, entonces lo que contenga el campo `web` del modelo será el enlace.
- Con el atributo `onclick`, se combina la acción que indiques con el contenido del campo `fieldname`. Esto se entiende mejor con un ejemplo:

#### Ejemplo con Widget Link:
```xml
<column name="web" order="130">
	<widget type="link" fieldname="web"/>
</column>
```
Si el campo `web` contiene `https://www.google.es`, el enlace apuntará a esa URL.

#### Ejemplo con Widget Text y onclick:
```xml
<column name="web" order="130">
	<widget type="text" fieldname="codcliente" onclick="https://www.google.es?q="/>
</column>
```
Si en el campo `web` tienes `'4567'`, el enlace apuntará a `/ruta-fs/https://www.google.es?q=?code=4567`.
