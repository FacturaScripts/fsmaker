# Método disableColumn() en Controladores Extendidos

> **ID:** 688 | **Permalink:** disablecolumn-192 | **Última modificación:** 15-04-2025
> **URL oficial:** https://facturascripts.com/disablecolumn-192

El método `disableColumn()` permite ocultar, deshabilitar o bloquear un campo o columna específico en las pestañas o vistas de un PanelController en FacturaScripts. Este método es muy útil para personalizar la interfaz del usuario, permitiendo mostrar u ocultar información según las necesidades del negocio.

## Uso Básico: Ocultar una Columna
Para ocultar una columna, basta con pasar el parámetro correspondiente en el método. Por ejemplo, en el siguiente código se desactiva la columna **customer** en la vista `ListFacturaCliente`:

```php
$this->tab('ListFacturaCliente')->disableColumn('customer', true);
```

Es fundamental utilizar el **nombre** exacto de la columna, tal y como se define en la vista XML. A continuación se muestra un ejemplo de definición de una columna en XML:

```xml
<column name="customer" order="120">
    <widget type="text" fieldname="nombrecliente" />
</column>
```

## Uso Avanzado: Configurar un Campo de Solo Lectura
El método `disableColumn()` también permite configurar un campo como solo lectura. Para ello, se utiliza un tercer parámetro que afecta la propiedad `readonly` del widget. Este parámetro puede tener los valores **'false'**, **'true'** o **'dinamic'**. En el siguiente ejemplo se establece el campo **email** como solo lectura en la vista `EditCliente`:

```php
$this->tab('EditCliente')->disableColumn('email', false, 'true');
```

## Notas Adicionales
- Asegúrate de utilizar los nombres de campo tal y como están definidos en la vista XML para evitar errores.
- La opción de solo lectura puede configurarse de forma dinámica evaluando el estado u otros parámetros en tiempo de ejecución.

Con estas configuraciones, puedes adaptar la interfaz a las necesidades específicas de tu negocio, mejorando la experiencia del usuario y la seguridad de la información mostrada.
