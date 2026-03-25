# Generación de PDF y Excel

> **ID:** 921 | **Permalink:** generacion-de-pdf | **Última modificación:** 05-12-2025
> **URL oficial:** https://facturascripts.com/generacion-de-pdf

Podemos usar la clase **ExportManager** para crear archivos PDF o Excel destinados a diversos fines, como albaranes, facturas, pedidos, o presupuestos, así como para generar listados personalizados.

### 1. Ejemplo: Imprimir una factura en PDF
Usaremos la clase ``ExportManager`` para generar un PDF con la factura.

```
use FacturaScripts\Dinamic\Lib\ExportManager;

// Creamos una instancia de FacturaCliente
$factura = new FacturaCliente();

// Cargamos los datos de la factura con código 123
if ($factura->loadFromCode(123)) {
	// La factura 123 existe
	
	// Creamos una instancia de ExportManager
	$export = new ExportManager();
	$export->newDoc('PDF');
	
	// Añadimos una página con la factura utilizando el método addBusinessDocPage()
	$export->addBusinessDocPage($factura);
	
	// Obtenemos el contenido del PDF generado
	$pdfContent = $export->getDoc();
	
	// Podemos almacenar el PDF según sea necesario
	if (file_put_contents('nombre_del_archivo.pdf', $pdfContent)) {
		// Archivo PDF generado correctamente
	} else {
		// Ha ocurrido un error al guardar el archivo PDF
	}
	
	// Si estamos en un controlador, podemos devolver el PDF directamente al navegador
	$this->setTemplate(false);
	$export->show($this->response);
}
```

### 2. Ejemplo: Imprimir un listado en Excel
Podemos usar la clase ExportManager para generar un Excel e incluirle un listado.

```
use FacturaScripts\Dinamic\Lib\ExportManager;

// Creamos una instancia de ExportManager
$export = new ExportManager();
$export->newDoc('XLS');

// Obtenemos todos los clientes
$clientes = Cliente::all();

// Configuramos las columnas del listado de clientes
$columns = ['Código', 'Nombre', 'Teléfono', 'Email'];

// Creamos un array para almacenar las filas de datos de clientes
$rows = [];

// Recorremos todos los clientes y agregamos sus datos a las filas
foreach ($clientes as $cliente) {
    $rows[] = [$cliente->codcliente, $cliente->nombre, $cliente->telefono1, $cliente->email];
}

// Agregamos una página con una tabla que enumera los datos de los clientes
$export->addTablePage($columns, $rows, [], 'Listado de Clientes');

// Si estamos en un controlador, podemos devolver el PDF directamente al navegador
$this->setTemplate(false);

// devolvemos el Excel al navegador
$export->show($this->response);
```

## Crear un PDF, Excel o CSV
Con la clase ``ExportManager``, podemos crear PDFs, Excel o archivos csv. Solamente debemos indicar el tipo al llamar al método ``newDoc()``:

```
$export = new ExportManager();
$export->newDoc('PDF'); // XLS para Excel y CSV para archivos csv
```

### Añadir una factura, albarán, pedido o presupuesto
Con la función ``addBusinessDocPage()`` de la clase ``ExportManager`` podemos añadir al PDF o Excel un documento de compra o venta.

```
// cargamos un pedido
$pedido = new PedidoCliente(),
$pedido->loadFromCode(1234);

// añadimos el pedido al ExportManager
$export->addBusinessDocPage($pedido);
```

### Añadir otros modelos
Con el método ``addModelPage()`` de la clase ``ExportManager`` podemos añadir cualquier modelo al PDF o Excel. Nos mostrará todas sus propiedades con sus valores, de forma muy genérica.

```
// cargamos un producto
$producto = new Producto(),
$producto->loadFromCode(1234);

// añadimos el producto al ExportManager
$export->addModelPage($producto);
```

### Añadimos un listado de un modelo
Con el método ``addListModelPage()`` de la clase ``ExportManager`` podemos añadir un listado de registros de un mismo modelo. Por ejemplo: un listado de productos.

```
$model = new Producto();
$where = [];
$orderBy = [];
$offset = 0;
$manager->addModelPage($model, $where, $orderBy, $offset);
```

### Añadir una tabla
Para añadir una tabla al PDF o Excel podemos usar el método ``addTablePage()`` de la clase ``ExportManager``.

```
$manager->addTablePage($headers, $rows);
```
