# Personalizando con Settings

> **ID:** 689 | **Permalink:** personalizando-con-settings-276 | **Última modificación:** 11-03-2026
> **URL oficial:** https://facturascripts.com/personalizando-con-settings-276

Las pestañas de los **controladores extendidos** disponen de la propiedad Settings, que es accesible mediante los métodos **getSettings** y **setSettings** que nos permiten leer y añadir/modificar la configuración de la pestaña, como por ejemplo los botones de nuevo, eliminar, imprimir, etc.

## 🚫 Desactivar/ocultar la pestaña
```
$this->tab('MyView')->setSettings('active', false);
```

## 🖱️ Desactivar/ocultar el botón nuevo
```
$this->tab('MyView')->setSettings('btnNew', false);
```

## 💾 Desactivar/ocultar el botón guardar
```
$this->tab('MyView')->setSettings('btnSave', false);
```

## ❌ Desactivar/ocultar el botón deshacer
```
$this->tab('MyView')->setSettings('btnUndo', false);
```

## 🗑️ Desactivar/ocultar el botón eliminar
```
$this->tab('MyView')->setSettings('btnDelete', false);
```

## 🖨️ Desactivar/ocultar el botón imprimir
```
$this->tab('MyView')->setSettings('btnPrint', false);
```

## 📝 Cambiar el número de elementos
Por defecto todos los listados usan el número de elementos por lista configurado en el panel de control, pero podemos establecer un número de elementos distinto para una pestaña modificando su propiedad `itemLimit`:

```
$this->tab('MyView')->setSettings('btnPrint', false);
```

## Opciones exclusivas de ListView
### ☑️ Desactivar/ocultar los checkboxes
Las vistas ListView muestran una columna de checkboxes en la parte izquierda para poder seleccionar y eliminar o realizar otras acciones. Si deseamos desactivarlo, podemos poner checkBoxes a false:
```
$this->tab('MyView')->setSettings('checkBoxes', false);
```

### 🖲️ Desactivar/ocultar el click sobre los elementos de la lista
Al hacer clic sobre un elemento de la lista nos redirecciona a dicho elemento. Si deseamos desactivarlo, podemos poner clickable a false:
```
$this->tab('MyView')->setSettings('clickable', false);
```

### 🔍 Desactivar/ocultar la búsqueda en el megabuscador
El megabuscador de FacturaScripts realiza búsquedas en todas las pestañas de todos los controladores que comienzan por List. Si desea desactivar la búsqueda en alguna de las pestañas, indíquelo de esta forma:
```
$this->tab('MyView')->setSettings('megasearch', false);
```
