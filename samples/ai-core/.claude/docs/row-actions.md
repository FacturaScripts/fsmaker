# Row actions

> **ID:** 648 | **Permalink:** row-actions-315 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/row-actions-315

Este tipo de row permite definir un grupo de **botones** a mostrar junto al resto de botones de la pestaña. Dependiendo del tipo de pestaña se visualizarán en un sitio distinto.

## Ejemplo:
```
<rows>
   <row type="actions">
      <button action="boton1" color="warning" icon="fas fa-vial" label="button-1" type="action"/>
      <button action="boton2" color="warning" icon="fas fa-terminal" label="button-2" type="action"/>
   </row>
</rows>
```

### 🖱️ Botones
Los botones se definen mediante **etiquetas button** y tienen las siguiente propiedades:
- **type**: indica el tipo de botón.
	- **action**: al hacer clic se recargará la página ejecutando el action indicado en la propiedad action. Este action deberá estar implementado en el controlador, ya sea en [execPreviousActions() o execAfterActions()](/publicaciones/controladores-extendidos-367).
	- **js**: al hacer clic ejecutará la función javascript indicada en la propiedad action.
	- **link**: al hacer clic se redirecciona a la página indicada en la propiedad action.
	- **modal**: al hacer clic mostrará el modal con el name indicado en la propiedad action.
- **id**: (opcional) identificador html para poder selecionarlo desde JavaScript.
- **icon**: (opcional) [icono del botón](/publicaciones/iconos-disponibles-308).
- **label**: texto a mostrar en el botón. Se traducirá automáticamente por FacturaScripts.
- **level**: (opcional) nivel de seguridad aplicable. Por defecto 0. Solamente los usuarios con un nivel de seguridad igual o superior podrán ver este botón.
- **color**: (opcional) indica la configuración de color a utilizar.
- **action**: indica la acción que se envía al controlador, función JavaScript o nombre del modal a mostrar.
- **confirm**: (opcional) si está a true mostrará al usuario una ventana solicitando confirmación de que desea ejecutar la acción.

### 🎨 Colores
- info: azul
- success: verde
- warning: amarillo
- danger: rojo
- dark: gris oscuro
- light: gris claro
- secondary: negro

## Añadir botones desde controladores
También puede añadir un botón desde su **ListController** o **EditController**. Simplemente use el método **addButton()**.
```
$this->addButton('ListProducto', [
	'action' => 'test-action',
	'icon' => 'fas fa-question',
	'label' => 'test'
]);
```
Este código añade el botón test a la pestaña ListProducto. Al hacer clic ejecutará la acción test-action del controlador, si la hubiera. Recuerde implementar esta acción en [execPreviousActions() o execAfterActions()](/publicaciones/controladores-extendidos-367).

Es posible indicar el grupo o row donde se añadirá el botón informando el identificador 'row' y como valor el name que identifica al row.
Si no se informa el botón se añade al row de acciones generales.
```
$this->addButton('EditEjercicio', [
	'row' => 'footer-actions',
	'action' => 'import-accounting',
	'color' => 'warning',
	'icon' => 'fas fa-file-import',
	'label' => 'import-accounting-plan',
	'type' => 'modal'
]);
```
Este código añade el botón importar al row footer con identificador 'footer-actions' y mostrará la ventana modal con identificador 'import-accounting'.
