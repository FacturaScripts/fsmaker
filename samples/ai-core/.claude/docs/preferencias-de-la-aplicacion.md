# Cómo crear preferencias de aplicación para su plugin

> **ID:** 693 | **Permalink:** preferencias-de-la-aplicacion-314 | **Última modificación:** 13-03-2025
> **URL oficial:** https://facturascripts.com/preferencias-de-la-aplicacion-314

Si necesita añadir opciones de configuración a su plugin, puede crear su propia sección en el apartado **Por defecto** (menú administrador > panel de control) de FacturaScripts. El controlador que gestiona estas secciones es **EditSettings**, un controlador especial que carga automáticamente una sección para cada archivo XML dentro de **XMLView** cuyo nombre comience con el prefijo **Settings**.

Ejemplos de archivos XML válidos:

```
default -> SettingsDefault.xml
email -> SettingsEmail.xml
logs -> SettingsLog.xml

myplugin -> SettingsMyPlugin.xml
```

## Creación del archivo XML
Para crear una nueva sección de configuración, debe crear un archivo llamado **SettingsMyPlugin.xml** en la carpeta **XMLView** de su plugin. Recuerde que, al añadir un nuevo archivo XML, debe regenerar la configuración dinámica (menú administrador > Plugins > botón Reconstruir).

Aquí tiene un ejemplo del contenido que debe tener el archivo:

```
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="data" numcolumns="12">
            <column name="name" display="none" order="0">
                <widget type="text" fieldname="name" readonly="true" required="true" />
            </column>
            <column name="email" order="100">
                <widget type="text" fieldname="email" icon="fas fa-envelope" />
            </column>
        </group>
    </columns>
</view>
```

En este ejemplo, hemos nombrado el archivo **SettingsMyPlugin.xml** siguiendo el ejemplo de **MyPlugin**, pero puede darle cualquier nombre, siempre que inicie con **Settings**, sea un archivo XML y se encuentre dentro de la carpeta **XMLView**.

### Columna 'name' Obligatoria
Los archivos de configuración deben incluir obligatoriamente una columna **name** para garantizar un funcionamiento correcto:

```
<column name="name" display="none" order="0">
   <widget type="text" fieldname="name" readonly="true" required="true" />
</column>
```

### Leer configuración
Puede acceder a los valores de cualquiera de los campos de configuración de su plugin utilizando `Tools::settings()` desde cualquier controlador o modelo:

```
use FacturaScripts\Core\Tools;

Tools::settings('myplugin', 'nombre-campo');
```

## No utilice EditSettings si...
Como ha visto, **EditSettings** permite añadir opciones de configuración simplemente añadiendo un archivo XMLView con el prefijo **Settings**. Sin embargo, si busca implementar funcionalidades más complejas, es recomendable crear su propio controlador.
