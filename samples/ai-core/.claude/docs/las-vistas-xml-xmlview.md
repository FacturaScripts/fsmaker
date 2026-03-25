# Las vistas XML (XMLView)

> **ID:** 643 | **Permalink:** las-vistas-xml-xmlview-668 | **Última modificación:** 31-12-2024
> **URL oficial:** https://facturascripts.com/las-vistas-xml-xmlview-668

Los controladores extendidos como [ListController](/publicaciones/listcontroller-232) y [EditController](/publicaciones/editcontroller-642), utilizan **archivos XML** para definir las columnas, grupos, widgets y botones a mostrar en una pestaña. De esta forma podemos personalizar rápidamente un listado o formulario sin necesidad de editar PHP. Estos archivos se deben almacenar en la **carpeta XMLView** del plugin.

## Estructura del XML
El elemento raíz del archivo XML será la **etiqueta view** y se podrán incluir las siguientes etiquetas a modo de grupo:
- [Etiqueta columns](/publicaciones/columns-88): (obligatoria) para definir la lista de campos que se visualizan en la vista.
- [Etiqueta rows](/publicaciones/rows-304): (opcional) permite definir condiciones especiales para la filas, así como añadir botones a las vistas.
- [Etiqueta modals](/publicaciones/modals-718): (opcional) define un formulario modal que será visualizado mediante la interacción con un botón definido en la vista.

### Ejemplo: vista para ListController
Aquí podemos ver que se definen 3 columnas a mostrar, más un row status, que sirve para indicar los colores a aplicar.
```
<?xml version='1.0' encoding='UTF-8'?>
<view>
    <columns>
        <column name='code' order='100'>
            <widget type='text' fieldname='codigo' />
        </column>
        <column name='description' order='105'>
            <widget type='text' fieldname='descripcion' />
        </column>
        <column name='state' display='center' order='110'>
            <widget type='text'>
                <option color='success' fieldname='estado'>ABIERTO</option>
                <option color='warning' fieldname='estado'>CERRADO</option>
            </widget>
        </column>
    </columns>
    <rows>
        <row type='status'>
            <option color='info' fieldname='estado'>Pendiente</option>
            <option color='warning' fieldname='estado'>Parcial</option>
        </row>
    </rows>
</view>
```

### Ejemplo: vista para EditController
Aquí podemos ver que se definen dos grupos de columnas para este formulario.
```
<?xml version='1.0' encoding='UTF-8'?>
<view>
    <columns>
        <group name='data' numcolumns='8' title='Identificación internacional' icon='fa-globe'>
            <column name='code' numcolumns='4' order='100'>
                <widget type='text' fieldname='codigo' />
            </column>
            <column name='description' numcolumns='8' order='105'>
                <widget type='text' fieldname='descripcion' />
            </column>
        </group>
        <group name='state' numcolumns='4'>
            <column name='state' display='center' order='100'>
                <widget type='text'>
                    <option color='success' fieldname='estado'>ABIERTO</option>
                    <option color='warning' fieldname='estado'>CERRADO</option>
                </widget>
            </column>
        </group>
    </columns>
</view>
```

### ¿Los cambios no se aplican?
Tenga en cuenta que durante el desarrollo del plugin, los archivos de Dinamic no se actualizan a menos que pulse el **botón reconstruir** del **menú Administrador, Plugins**.

Además, como a nivel de usuario puede personalizar los listados y formularios desde el **botón opciones** de cada pantalla, si después hace cambios en el XML, no se verán reflejados. Vaya al botón opciones de esa pantalla y después elimine los cambios.
