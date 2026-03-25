# Extensiones de Vistas HTML

> **ID:** 1311 | **Permalink:** extensiones-de-vistas-html | **Última modificación:** 03-12-2024
> **URL oficial:** https://facturascripts.com/extensiones-de-vistas-html

Podemos añadir ubicaciones a nuestras plantillas twig de tal forma que otro plugin pueda añadir contenido en esa ubicación. **NO es herencia**, no estamos machacando los datos originales, estamos añadiendo contenido extra en una ubicación determinada.

Al crear la plantilla Twig tenemos que proporcionar a dicha plantilla las ubicaciones en las que permitimos que otros programadores puedan extender y añadir contenido.

### Añadir ubicaciones
Esto es un ejemplo de como crear una plantilla llamada **MiPlantilla** con ubicaciones. Llamamos la funcion ``getIncludeViews()``, donde tendremos que pasarle el nombre de la propia plantilla y el nombre de la posición (la posición es un nombre que nos inventaremos).
```
<html>
	<head>
    {% for includeView in getIncludeViews('MiPlantilla', 'head') %}
        {% include includeView['path'] %}
    {% endfor %}
	</head>
	<body>
    {% for includeView in getIncludeViews('MiPlantilla', 'body') %}
        {% include includeView['path'] %}
    {% endfor %}
		<div id="menu">
        {% for includeView in getIncludeViews('MiPlantilla', 'menu') %}
            {% include includeView['path'] %}
        {% endfor %}
		</div>
	</body>
</html>
```
Podremos añadir todas ubicaciones que queramos en nuestras plantillas, para dar mejor y mayor integración con plugins de terceros.

### Extender desde un plugin
Ahora desde nuestro plugin vamos a añadir contenido en la plantilla anterior. Dentro de la carpeta **Extension/View** debemos crear el archivo **MiPlantilla_head.html.twig**. Para cada ubicación que queramos extender tendremos que crear el archivo, por ejemplo: *MiPlantillabody.html.twig y MiPlantillamenu.html.twig.*

Además podriamos añadir una ordenación a los archivos twig. Imaginemos que varios plugins extienden y añaden contenido al mismo archivo ¿que pasa?, por defecto todos los archivos se ordenan por orden alfabético y un número 10 al final, si no hemos establecido nosotros el número. Si queremos asegurarnos que nuestra extesión se cargue antes o después solo debemos renombrar el archivo así: **MiPlantilla_head_05.html.twig, MiPlantilla_body_13.html.twig y MiPlantilla_menu_09.html.twig**.

Notese la **nomenclatura obligatoria** de los archivos: NOMBRE_PLANTILLA_UBICACION TWIG_ORDENACION.html.twig

Ahora en cualquiera de nuestros archivos twig para extender añadimos nuestro código personalizado.

MiPlantilla_head_05.html.twig
```
<title>Extensiones de Vistas HTML</title>
<meta name="description" content="Descripción de la página"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="generator" content="FacturaScripts"/>
```

MiPlantilla_menu_09.html.twig
```
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Features</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Pricing</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Disabled</a>
      </li>
    </ul>
  </div>
</nav>
```
