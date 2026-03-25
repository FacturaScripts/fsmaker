# addGridView() - (obsoleto)

> **ID:** 686 | **Permalink:** addgridview-524 | **Última modificación:** 29-05-2025
> **URL oficial:** https://facturascripts.com/addgridview-524

Añade una vista para editar un registro *padre* de un modelo y múltiples registros *hijos* de un modelo. La edición de los registros hijos se realiza mediante el componente handsontable que nos permite editar los datos a modo de hoja de cálculo. Sólo es posible tener una única vista Grid dentro de un PanelController. Se usa dentro de la función **createViews()** del PanelController.  Debido a la necesidad de enlazar la vista padre con su detalle este método difiere de los otros métodos usados para añadir vistas. En este caso debemos informar con un array la vista padre y la hija. El array debe contener las claves **name** y **model** junto con sus valores.

``OBSOLETO: fue eliminado de FacturaScripts 2022``

El tratamiento de las dos vistas (padre y detalle) se realiza de manera conjunta por lo que en caso de utilizar un EditController como base de nuestro controlador **no debemos** llamar al método padre en createViews (esto crearía dos veces la vista padre).

Para el correcto renderizado de estás vistas es necesario usar la plantilla GridView o una que herede de esta. Esta sólo es utilizada cuando el modelo padre tiene un registro de datos. En caso de ser un alta nueva, se utiliza la plantilla EditView visualizando sólo el formulario para introducir los datos del padre y al grabar se refrescará la página visualizando el grid de datos.

```
protected function createViews()
{
    $master = ['name' => 'EditAsiento', 'model' => 'Asiento'];
    $detail = ['name' => 'EditPartida', 'model' => 'Partida'];
    $this->addGridView($master, $detail, 'accounting-entry', 'fas fa-balance-scale');
    $this->views['EditAsiento']->template = 'EditAsiento.html.twig';
    $this->setTabsPosition('bottom');
}
```

El modelo que se indica para los datos padre debe implementar la interface GridModelInterface. Dicha interface obliga a implementar los métodos accumulateAmounts y initTotals encargados del cálculo de importes totales. En caso de no necesitar importes totales se deben implementar vacíos.

El método accumulateAmounts recibe un array con los datos del registro detalle que se está procesando.

```
/// Acumular importes en su total
public function accumulateAmounts(array &$detail)
{
    $haber = isset($detail['haber']) ? (float) $detail['haber'] : 0.0;
    $this->importe += round($haber, (int) FS_NF0);
}

/// Inicializar atributos de totales
public function initTotals()
{
    $this->importe = 0.00;
}
```

## Personalización:

La plantilla GridView añade la carga de archivos y la creación de los objetos necesarios para gestionar el grid de datos, y creando un nuevo bloque denominado **gridcard** donde inserta el grid. Los datos son cargados en una variable de JavaScript denominada **documentLineData** y la visualización se realiza dentro de un card de bootstrap, en el bloque body con el identificador **document-lines**.

```
{% block gridcard %}
    <div class="col">
        {# Grid data panel #}
        <div class="card">
            <div class="card-body p-0">
                <div id="document-lines"></div>
            </div>
        </div>
    </div>
{% endblock %}
```

Aunque estas tareas se realizan de manera automática es posible personalizar la apariencia creando nuestras propias plantillas de manera sencilla heredando de la plantilla base y sobrescribiendo el bloque *gridcard*. En este caso debemos asegurarnos que nuestra plantilla incluya una división con el identificador *document-lines* dónde se incluirá el grid.

```
{% block gridcard %}
    <div class="col-9 mr-2">
        <div class="card">
            <div class="card-header">
                <span><small id="account-description"></small></span>
                <span class="float-right"><small><strong>{{ i18n.trans('unbalance') }}: <span id="unbalance">0.00</span></strong></small></span>
            </div>
            <div class="body">
                <div id="document-lines"></div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Establecer automatismos:

La vista lleva incorporado un gestor de eventos que hacen de intermediario entre nuestro código y el componente HandsOnTable, simplificando la personalización y evitando que tengamos que conocer en profundidad el componente. La propia vista utiliza algunos de estos eventos para su correcto funcionamiento, por lo que si se realizan configuraciones sobre el componente HandsOnTable directamente, puede dejar de funcionar correctamente.

Para añadir un control sobre el Grid añadiremos un archivo de JavaScript con el nombre del controlador en la carpeta *Assets/JS* que será cargado automáticamente junto con la vista. En el evento **$(document).ready** de nuestro archivo introduciremos los eventos a controlar realizando una llamada a la función **addEvent** por cada evento a controlar.

```
$(document).ready(function () {
    // Controla que se haya cargado el componente Grid
    if (document.getElementById("document-lines")) {
        // Añade eventos al gestor de eventos
        addEvent("afterChange", customAfterChange);
        addEvent("afterSelection", customAfterSelection);
    }
});
```

### addEvent
Esta función añade un evento al gestor de eventos. En la llamada indicaremos el nombre del evento a controlar y la función que se ejecutará cuando se lance el evento. Los eventos de estado que se pueden controlar tienen dos partes.

- **before**: Se ejecuta antes de iniciar el evento indicado.
- **after**: Se ejecuta después de completarse el evento indicado.

Así el evento con nombre **beforeChange** se ejecutará antes de comenzar la edición de una celda, mientras que el evento **afterChange** se ejecutará después de terminar la edición. Algunos ejemplos de eventos. Más información en la documentación del componente [HandsOnTable](https://handsontable.com/docs/7.0.2/Hooks.html#event).

- **BeginEditing**:	Se activa cuando el editor se abra y se procesa.
- **Change**:	Se ejecuta cuando una o más celdas hayan sido cambiadas. Por razones de rendimiento, la matriz de cambios es null para durante el evento loadData.
- **ColumnMove**:	Se ejecuta cuando se cambia el orden de los índices visuales de una columna.
- **ColumnResize**:	Se ejecuta cuando se cambia el tamaño de una columna.
- **Copy**:	Al hacer un copiar hacia el portapapeles.
- **Cut**:	Al hacer un cortar hacia el portapapeles.
- **Paste**:	Al pegar el contenido del portapapeles.
- **Undo**:	Al deshacer un cambio.
- **Select**:	Al seleccionar una celda o fila.
- **Deselect**:	Al deseleccionar una celda o fila.
- **OnCellMouseDown**: Al pulsar el botón del ratón sobre una celda.
- **OnCellMouseOver**: Al pasar el cursor del ratón sobre una celda.

## Métodos incorporados
Además del gestor de eventos, las vistas Grid incorporan una serie de funciones JavaScript para facilitar la programación de tareas personalizadas.

- **getGridColumnName**: Obtiene el nombre de campo asociado a una columna.
- **getGridData**:	Nos retorna un array con la estructura de datos. Se puede indicar el nombre de campo donde almacenar el índice del orden actual de las líneas
- **getGridFieldData**: Para obtener el valor de una celda. Devemos indicar el indice de la fila y el nombre de campo.
- **getGridRowValues**: Nos retorna un array con los datos de la fila indicada.
- **setGridRowValues**: Establece los valores informados en un array a una fila. El array con los datos debe estar formado por las claves “field” y “value” por cada columna que deseamos cambiar.
- **selectCell**:	Selecciona una celda o un rango de celdas.
- **deselectCell**:	Deselecciona todas las celdas.
- **getRowSelected**:	Obtiene la fila seleccionada.
- **getColumnSelected**: Obtiene la celda seleccionada.

### Ejemplos de uso
```
// Selecionar fila y cambiar sus valores
var selectedRow = getRowSelected();
if (selectedRow !== null) {
    var vatBody = $("#modal" + idmodal).find(".modal-body");
    var values = [
        {"field": "documento", "value": vatBody.find(".form-group input[name=\"documento\"]").val()},
        {"field": "cifnif", "value": vatBody.find(".form-group input[name=\"cifnif\"]").val()},
        {"field": "baseimponible", "value": vatBody.find(".form-group input[name=\"baseimponible\"]").val()},
        {"field": "iva", "value": vatBody.find(".form-group input[name=\"iva\"]").val()},
        {"field": "recargo", "value": vatBody.find(".form-group input[name=\"recargo\"]").val()}
    ];
    setGridRowValues(selectedRow, values);
}

// Cargar datos del grid a un formulario modal
var values = getGridRowValues(selectedRow);
var vatBody = $("#modal" + idmodal).find(".modal-body");
vatBody.find(".form-group input[name=\"cifnif\"]").val(values["cifnif"]);
vatBody.find(".form-group input[name=\"baseimponible\"]").val(values["baseimponible"]);
vatBody.find(".form-group input[name=\"iva\"]").val(values["iva"]);
vatBody.find(".form-group input[name=\"recargo\"]").val(values["recargo"]);

// Seleccionar la primera celda del grid
selectCell(0, 0);

// Deseleccionar todas las celdas
deselectCell();
```
