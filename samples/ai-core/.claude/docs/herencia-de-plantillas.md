# Herencia de plantillas

> **ID:** 642 | **Permalink:** herencia-de-plantillas-904 | **Última modificación:** 29-05-2025
> **URL oficial:** https://facturascripts.com/herencia-de-plantillas-904

Para que su plantilla herede de otra plantilla twig, simplemente debe usar la función extends:

## Herencia simple
```
{% extends 'Master/MenuTemplate.html.twig' %}

{% block body %}
	<h1>Hola mundo</h1>
{% endblock %}
```
Esta vista hereda de *Master/MenuTemplate.html.twig*, que es la vista de FacturaScripts que incluye el menú superior. Si por el contrario no queremos el menú, podemos heredar de *Master/MicroTemplate.html.twig*

## Reemplazar una plantilla y heredar de ella
Si lo que desea es reemplazar una plantilla, pero heredando de esa misma plantilla, entonces debe usar el **identificador @** para indicar dónde buscar la plantilla:
- Si la plantilla está en Core, el identificador es @Core/
- Si la plantilla está en un plugin, el identificador es @Plugin**NombrePlugin**/

### Ejemplo con @Core/
```
{% extends '@Core/Master/MenuTemplate.html.twig' %}

{% block body %}
	<h1>Hola mundo</h1>
{% endblock %}
```

### Ejemplo con @PluginNombrePlugin
```
{% extends "@Pluginecommerce/ShoppingCart.html.twig" %}

{% block body %}
	<h1>Hola mundo</h1>
{% endblock %}
```
