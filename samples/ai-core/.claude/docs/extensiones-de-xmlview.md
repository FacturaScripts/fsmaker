# Extensiones de XMLView

> **ID:** 917 | **Permalink:** extensiones-de-xmlview | **Última modificación:** 07-11-2025
> **URL oficial:** https://facturascripts.com/extensiones-de-xmlview

Para modificar o añadir columnas a un XMLView de otro plugin (o del núcleo), podemos crear una extensión. Esto implica crear un archivo XML con los cambios y colocarlo en la carpeta **Extension/XMLView** de nuestro plugin.

## Ejemplo: Añadir columnas a un XMLView
Imaginemos que hemos añadido la columna "usado" al producto. Para incluir esta columna en la vista del producto, utilizaremos un widget de tipo checkbox y lo incluiremos en una extensión en el archivo **Extension/XMLView/EditProducto.xml**:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="options" numcolumns="12" valign="bottom">
            <column name="usado">
                <widget type="checkbox" fieldname="usado" />
            </column>
        </group>
    </columns>
</view>
```

De esta manera, estamos indicando a FacturaScripts que incluya la columna "usado" dentro del grupo "options" de la lista de columnas del archivo `XMLView/EditProducto.xml`.

## Ejemplo: Editar columnas en un XMLView
Ahora, imaginemos que deseamos editar una columna ya creada previamente. Podemos modificarla utilizando el atributo **overwrite="true"** y lo incluiremos en una extensión en el archivo **Extension/XMLView/EditProducto.xml**:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="options" numcolumns="12" valign="bottom">
            <column name="usado" overwrite="true">
                <widget type="select" fieldname="usado" translate="true" required="true">
                    <values title="book">-2</values>
                    <values title="subtract">-1</values>
                    <values title="do-nothing">0</values>
                    <values title="add">1</values>
                    <values title="foresee">2</values>
                </widget>
            </column>
        </group>
    </columns>
</view>
```

De este modo, estamos diciendo a FacturaScripts que edite la columna "usado" dentro del grupo "options" de la lista de columnas del archivo `XMLView/EditProducto.xml`. En este caso, hemos reemplazado el widget de tipo checkbox por un widget de tipo select.

### Nota
Cuando la vista que se está extendiendo ha sido modificada directamente desde el botón de opciones, puede que no se muestre el campo añadido, ya que prevalecerá el estado de la vista modificada en la base de datos.
