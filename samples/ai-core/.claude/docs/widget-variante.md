# Widget Variante

> **ID:** 1631 | **Permalink:** widget-variante | **Última modificación:** 31-10-2025
> **URL oficial:** https://facturascripts.com/widget-variante

El widget Variante permite seleccionar un producto o variante desde el catálogo de productos, donde podemos buscar y filtrar por fabricante y familia.

```
<column name="reference" numcolumns="3" order="110">
	<widget type="variante" fieldname="referencia"/>
</column>
```

Por defecto usa el campo **referencia**, pero podemos indicar otro campo como idproducto añadiendo el parámetro ``match``:

```
<column name="product" numcolumns="3" order="110">
	<widget type="variante" fieldname="idproducto" match="idproducto"/>
</column>
```

Así es como se ve el widget en un formulario de edición.

![widget listado productos](https://i.imgur.com/u07qfy4.png)

Muestra un botón con la referencia del producto seleccionado, al hacer clic mostrará un modal con el **catálogo de productos**, donde podremos buscar y filtrar por fabricante y familia:

![ventana productos](https://i.imgur.com/6jzhIzG.png)
