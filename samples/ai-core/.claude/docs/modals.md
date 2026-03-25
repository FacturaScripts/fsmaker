# Modals (XMLView)

> **ID:** 651 | **Permalink:** modals-718 | **Última modificación:** 28-05-2025
> **URL oficial:** https://facturascripts.com/modals-718

Los formularios modales son vistas complementarias a la vista principal, que permanecen ocultas hasta que se pulsa su **botón de tipo modal**. Estos formularios se declaran de manera muy similar a lo detallado en la sección COLUMNS. Podemos definir todos los modals que necesitemos, simplemente añadiendo grupos (etiqueta group) dentro de la etiqueta modals del XMLView.

## Ejemplo de modal:
```
<modals>
	<group name="test" title="other-data" icon="fas fa-users">
		<column name="name" numcolumns="12" description="desc-custommer-name">
			<widget type="text" fieldname="nombre" required="true" />
		</column>
		<column name="create-date" numcolumns="6">
			<widget type="date" fieldname="fechaalta" readonly="true" />
		</column>
		<column name="blocked-date" numcolumns="6">
			<widget type="date" fieldname="fechabaja" />
		</column>
	</group>
</modals>
```

### Mostrar un modal
Para mostrar un modal que ya hayamos definido en **modals** debemos definir un **botón de tipo modal** en un row type actions, header o footer. Además este botón debe indicar el nombre del modal en su **propiedad action**.

#### Ejemplo:
```
<rows>
	<row type="actions">
		<button type="modal" label="mostrar" color="warning" action="test" />
	</row>
</rows>
```

### Modal de distinto tamaño
Podemos mostrar una ventana de modal más pequeña añadiendo **class="modal-sm"** al grupo del modal. También podemos mostrar una ventana más grande con **class="modal-lg"** o **class="modal-xl"**.

### ModalInsert
También podemos hacer que al pulsar el botón nuevo en un listado aparezca un modal elegido, en lugar de redirigir al controlador del modelo. Para lograr esto solamente debemos indicar en el campo modalInsert el name del modal.

```
$this->setSettings($viewName, 'modalInsert', 'add-lote');
// en este caso al hacer clic en el botón nuevo se mostrará el modal con name 'add-lote'
```
