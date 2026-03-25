# Cómo obtener el esquema de un modelo mediante API

> **ID:** 2228 | **Permalink:** como-obtener-el-esquema-de-un-modelo-mediante-api | **Última modificación:** 06-10-2025
> **URL oficial:** https://facturascripts.com/como-obtener-el-esquema-de-un-modelo-mediante-api

La API de FacturaScripts, además de listar, crear, modificar y eliminar registros de modelos, también permite obtener el esquema de la tabla. Para ello simplemente hay que usar el identificador scheme después del endpoint.

## Ejemplo
En este ejemplo obtendremos el esquema de la tabla del modelo Familia.

```
http://localhost:8000/api/3/familias/schema
```

La API nos devolverá la lista de campos de la tabla:

```
{
	"codfamilia": {
		"type": "varchar(8)",
		"default": null,
		"is_nullable": "NO"
	},
	"codsubcuentacom": {
		"type": "varchar(15)",
		"default": null,
		"is_nullable": "YES"
	},
	"codsubcuentairpfcom": {
		"type": "varchar(15)",
		"default": null,
		"is_nullable": "YES"
	},
	"codsubcuentaven": {
		"type": "varchar(15)",
		"default": null,
		"is_nullable": "YES"
	},
	"descripcion": {
		"type": "varchar(100)",
		"default": null,
		"is_nullable": "NO"
	},
	"madre": {
		"type": "varchar(8)",
		"default": null,
		"is_nullable": "YES"
	},
	"numproductos": {
		"type": "int(11)",
		"default": "0",
		"is_nullable": "NO"
	}
}
```
