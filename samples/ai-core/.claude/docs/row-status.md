# Row status (XMLView)

> **ID:** 646 | **Permalink:** row-status-477 | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/row-status-477

El tipo de estado permite **colorear las filas en función del valor de un campo** del registro o de una serie de condiciones. Se declara mediante la inclusión de una relación de uno o varios registros **option**, indicando la configuración que se aplicará para la fila. Los atributos que se pueden especificar son:

- **color** (obligatorio): Para indicar el color deseado.
- **fieldname**: Indica sobre qué campo se valida la opción.
- **title**: Texto identificativo para el usuario de la opción.

```xml
<rows>
	<row type="status">
		<option color="success" fieldname="estado" title="open">ABIERTO</option>
		<option color="warning" fieldname="estado" title="closed">CERRADO</option>
	</row>
</rows>
```

### 🎨 Colores
Los colores para la selección provienen de la biblioteca Bootstrap:
- **info**: azul
- **success**: verde
- **warning**: amarillo
- **danger**: rojo
- **light**: gris claro
- **secondary**: negro

### 🧮 Operadores
Se pueden usar los siguientes operadores en el valor de la etiqueta *option*:
- `gt:`: Se aplica si el valor del campo del modelo es **mayor que** el valor indicado.
- `gte:`: Se aplica si el valor del campo del modelo es **mayor o igual que** el valor indicado.
- `lt:`: Se aplica si el valor del campo del modelo es **menor que** el valor indicado.
- `lte:`: Se aplica si el valor del campo del modelo es **menor o igual que** el valor indicado.
- `neq:`: Se aplica si el valor del campo del modelo es **distinto de** el valor indicado.
- `null:`: Se aplica si el valor del campo del modelo **es nulo**.
- `notnull:`: Se aplica si el valor del campo del modelo **no es nulo**.

En cualquier otro caso, se realizará una comprobación de igualdad, es decir, que el valor del campo del modelo sea **igual** al valor indicado.

También se puede usar el comodín `field:XXX` para comparar con el valor de otra columna.

### Declaración de las Condiciones
Para declarar condiciones, se pueden utilizar los siguientes métodos:
- Un único campo: Se declara el atributo **fieldname** dentro de la declaración del **row**, indicando el nombre del campo que contendrá los valores.
- Varios campos: Se declara el atributo **fieldname** dentro de la declaración del **option**, indicando el nombre del campo que contendrá los valores.
- Ambos: Se declara el atributo **fieldname** dentro de **row** y dentro de los **option** que no utilicen el campo general indicado dentro de *row*.

### Ejemplo para Condiciones con un Mismo Campo
```xml
<rows>
	<row type="status" fieldname="estado">
		<option color="info" title="pending">Pendiente</option>
		<option color="warning" title="partial">Parcial</option>
	</row>
</rows>
```
- Pinta la fila de color azul si el campo **'estado'** es **'Pendiente'**.
- Pinta la fila de color amarillo si el campo **'estado'** es **'Parcial'**.

#### Ejemplo para Condiciones con Distintos Campos y Valores
```xml
<rows>
	<row type="status">
		<option color="info" fieldname="nostock">1</option>
		<option color="danger" fieldname="bloqueado">1</option>
		<option color="success" fieldname="stockfis">gt:1</option>
		<option color="warning" fieldname="stockfis">lt:1</option>
	</row>
</rows>
```
- Pinta la fila de color azul si el campo **'nostock'** es **'Verdadero'**.
- Pinta la fila de color rojo si el campo **'bloqueado'** es **'Verdadero'**.
- Pinta la fila de color verde si el campo **'stockfis'** es **mayor que 0**.
- Pinta la fila de color amarillo si el campo **'stockfis'** es **menor que 1**.

#### Ejemplo para Comparar con Otro Campo
```xml
<rows>
	<row type="status">
		<option color="danger" fieldname="disponible">lt:field:stockmin</option>
	</row>
</rows>
```
- Pinta la fila de color rojo si el campo **'disponible'** es menor que el valor del campo **'stockmin'**.

### Añadir Colores desde el Controlador
Desde `ListController`, también se pueden añadir colores a los listados:
```php
$this->addColor($viewName, 'nostock', 1, 'info', 'no controla stock');
```

Desde `EditController`, también se pueden añadir colores a los listados:
```php
$this->view[$viewName]->addColor('nostock', 1, 'info', 'no controla stock');
```

La **función addColor()** tiene los siguientes parámetros:
- **$fieldName**: Nombre del campo sobre el cual realizar la comprobación, igual a *fieldname* en el XML.
- **$value**: Valor a comprobar; el **$fieldName** se comparará con este valor.
- **$color**: Color a mostrar en la fila.
- **$title**: Texto a mostrar en la leyenda de los colores.
