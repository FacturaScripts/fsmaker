# Migrar los XML de las tablas de 2017

> **ID:** 706 | **Permalink:** migrar-los-xml-de-las-tablas-800 | **Última modificación:** 29-05-2025
> **URL oficial:** https://facturascripts.com/migrar-los-xml-de-las-tablas-800

Como hemos comentado, los archivos XML de las tablas que solían estar en **model/table** en las versiones 2015/2017, ahora deben estar en la carpeta **Table** del plugin.

## Cambios a realizar en el XML
La mayor parte de facturaScripts 2018 ha sido reescrita en inglés, y el resto se cambiará en futuras revisiones. Las etiquetas de los XML de las tablas también han sido reemplazadas por sus equivalentes en inglés:
- < tabla > es ahora < table >
- < columna > es ahora < column >
- < nombre > es ahora < name >
- < tipo > es < type >
- < nulo > es < null >
- < defecto > es < default >
- < restriccion > es < constraint >
- < consulta > es < type >

Puedes leer más sobre [los archivos XML de las tablas de FacturaScripts 2018](/publicaciones/la-definicion-de-la-estructura-de-la-tabla-514) en la documentación.

### Script de migración
Puedes copiar este archivo al directorio donde tengas los xml para hacer la transformación.
```
<?php
chdir(__DIR__);
foreach (scandir(__DIR__) as $filename) {
    if (is_file($filename) && substr($filename, -4) === '.xml') {
        $txt = file_get_contents($filename);
        $transform = [
            'tabla>' => 'table>',
            'columna>' => 'column>',
            'nombre>' => 'name>',
            'tipo>' => 'type>',
            'nulo>' => 'null>',
            'defecto>' => 'default>',
            'restriccion>' => 'constraint>',
            'consulta>' => 'type>',
        ];

        $final = strtr($txt, $transform);
        file_put_contents($filename, $final);
        echo $filename . '\n';
    }
}
```
