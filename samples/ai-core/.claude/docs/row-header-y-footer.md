# Row header y footer (XMLView)

> **ID:** 649 | **Permalink:** row-header-y-footer-565 | **Última modificación:** 18-03-2025
> **URL oficial:** https://facturascripts.com/row-header-y-footer-565

Los tipos de fila **header** y **footer** permiten definir paneles que se colocan en la cabecera o el pie de página de una pestaña, dependiendo de su tipo.

Para declarar un panel, utilizaremos la etiqueta **group**, donde podemos incluir etiquetas **button** según sea necesario. Cada apartado del panel se puede personalizar con los siguientes atributos:
- **name**: Identificador único para el grupo.
- **class**: Clases CSS que se aplicarán al panel.
- **title**: Texto que se mostrará como cabecera del panel.
- **label**: Texto que se mostrará en el cuerpo del panel.
- **footer**: Texto para el pie del panel.
- **html**: Plantilla Twig que se incluirá en el contenido del card.

## Ejemplo de Fila de Tipo Header
```
<rows>
	<row type='header'>
		<group name='footer1' footer='specials-actions' label='Esto es una muestra de botones'>
			<button type='modal' label='Modal' color='primary' action='test' icon='fas fa-users'/>
			<button type='action' label='Action' color='info' action='process1' icon='fas fa-book'/>
		</group>
	</row>
</rows>
```

## Ejemplo de Fila de Tipo Footer
```
<rows>
	<row type='footer'>
		<group name='footer_actions' footer='specials-actions'>
			<button type='action' label='add-all-enabled' color='info' action='add-api-access-enabled' icon='fas fa-plus'/>
			<button type='action' label='add-all-disabled' color='info' action='add-api-access-disabled' icon='fas fa-plus'/>
		</group>
	</row>
</rows>
```

### Botones
Los botones se definen mediante etiquetas **button** y poseen las siguientes propiedades:
- **type**: Especifica el tipo de botón.
	- **action**: Al hacer clic, se recargará la página ejecutando la acción indicada en esta propiedad. Esta acción debe estar implementada en el controlador.
	- **js**: Ejecuta la función JavaScript indicada en la propiedad action al hacer clic.
	- **link**: Redirige a la página especificada en la propiedad action al hacer clic.
	- **modal**: Muestra el modal cuyo nombre se indica en la propiedad action al hacer clic.
- **id**: Identificador HTML para su uso desde JavaScript.
- **icon**: [Icono del botón](https://facturascripts.com/publicaciones/iconos-disponibles-308).
- **label**: Texto que se mostrará en el botón. Este se traducirá automáticamente por FacturaScripts.
- **level**: Nivel de seguridad aplicable, siendo 0 por defecto. Solo los usuarios con un nivel de seguridad igual o superior podrán visualizar este botón.
- **color**: Configuración de color a aplicar.
- **action**: Acción que se enviará al controlador, función JavaScript o nombre del modal a mostrar.

### Colores Disponibles
- **info**: Azul
- **success**: Verde
- **warning**: Amarillo
- **danger**: Rojo
- **light**: Gris claro
- **secondary**: Negro
