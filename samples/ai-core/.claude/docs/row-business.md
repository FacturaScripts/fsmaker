# Row Business (Obsoleto)

> **ID:** 650 | **Permalink:** row-business-351 | **Última modificación:** 15-04-2025
> **URL oficial:** https://facturascripts.com/row-business-351

Este tipo de `row` permite añadir columnas en la parte inferior de la primera pestaña en los documentos de compra o de venta, como albaranes, facturas y pedidos.

## Ejemplo de uso

A continuación, se presenta un ejemplo de cómo implementar un `row` de tipo `business` en la configuración de tus documentos:

```xml
<rows>
	<row type="business">
		<column name="observations" numcolumns="12" order="100">
			<widget type="textarea" fieldname="observaciones" />
		</column>
	</row>
</rows>
```

En este ejemplo, estamos añadiendo una columna llamada `observations` de tipo `textarea`, que ocupa un ancho de 12 columnas. Esto permite a los usuarios agregar observaciones detalladas en el documento.

## Notas importantes
- Este tipo de `row` está actualmente obsoleto, por lo que se recomienda considerar alternativas actualizadas en la documentación de FacturaScripts.

## Recursos relacionados
Para más información sobre la migración de plugins antiguos, puedes consultar: [Migración de plugins antiguos](https://facturascripts.com/publication/migracion-de-plugins-de-2015-2017-822).
