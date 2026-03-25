# Cómo crear una factura, albarán, pedido o presupuesto

> **ID:** 1799 | **Permalink:** como-crear-una-factura-albaran-pedido-o-presupuesto | **Última modificación:** 01-02-2026
> **URL oficial:** https://facturascripts.com/como-crear-una-factura-albaran-pedido-o-presupuesto

Los documentos de compra o venta son más complejos, ya que se relacionan directamente con un cliente o proveedor, incluyen una o varias líneas y requieren el cálculo de totales.

## 🚚 Compras

A continuación se muestra un ejemplo de cómo crear un **presupuesto de compra**, para ello crearemos un objeto de la clase `PresupuestoProveedor`. El mismo procedimiento aplica para **pedidos**, **albaranes** y **facturas** de proveedor, solamente cambiaría la clase a utilizar:

- `PedidoProveedor`.
- `AlbaranProveedor`.
- `FacturaProveedor`.

Para este ejemplo supondremos que ya tenemos creado un proveedor y un producto:

```php
// Cargamos el proveedor con código "1"
$proveedor = new Proveedor();
if (!$proveedor->load('1')) {
    // No se encontró el proveedor
}

// Creamos el presupuesto asignando empresa, almacén, serie, divisa, forma de pago y fecha predeterminada
$presupuesto = new PresupuestoProveedor();
$presupuesto->setSubject($proveedor); // Asignamos el proveedor
if (!$presupuesto->save()) {
    // No se pudo guardar el presupuesto
}

// Añadimos una línea con un producto al presupuesto
$newLinea1 = $presupuesto->getNewProductLine('referencia1'); // Se asigna el producto, descripción, precio, etc.
$newLinea1->cantidad = 1;
if (!$newLinea1->save()) {
    // No se pudo guardar la línea
}

// Añadimos una línea de texto
$newLinea2 = $presupuesto->getNewLine();
$newLinea2->descripcion = 'Mano de obra';
$newLinea2->cantidad = 5;
$newLinea2->pvpunitario = 10;
if (!$newLinea2->save()) {
    // No se pudo guardar la línea
}

// Actualizamos los totales del presupuesto
$lineas = $presupuesto->getLines();
if (!Calculator::calculate($presupuesto, $lineas, true)) {
    // No se pudieron actualizar los totales
}
```

**Notas:**

- Para asignar el proveedor se utiliza el método `setSubject()`.
- Para agregar líneas de producto, utiliza `getNewProductLine('referencia')`, pasando como parámetro la referencia del producto.
- Para agregar líneas de texto (sin producto), utiliza `getNewLine()`.
- Finalmente, se invoca la clase `Calculator` para recalcular y actualizar los totales del documento. Recuerda incluir la directiva:

```php
use FacturaScripts\Core\Lib\Calculator;
```

## 🛒 Ventas

El siguiente ejemplo ilustra cómo crear una **factura de venta**, para ello crearemos un objeto de la clase `FacturaCliente`. El proceso es similar al de una compra, variando únicamente que asignamos un cliente. Este método es aplicable igualmente para **albaranes**, **pedidos** y **presupuestos** de venta, solamente habría que cambiar la clase a utilizar:

- `AlbaranCliente`.
- `PedidoCliente`.
- `PresupuestoCliente`.

Para este ejemplo supondremos que ya tenemos creado el cliente y el producto.

```php
// Cargamos el cliente con código "1"
$cliente = new Cliente();
if (!$cliente->load('1')) {
    // No se encontró el cliente
}

// Creamos la factura asignando empresa, almacén, serie, divisa, forma de pago y fecha predeterminada
$factura = new FacturaCliente();
$factura->setSubject($cliente); // Asignamos el cliente
if (!$factura->save()) {
    // No se pudo guardar la factura
}

// Añadimos una línea con un producto a la factura
$newLinea1 = $factura->getNewProductLine('referencia1'); // Se asigna el producto, descripción, precio, etc.
$newLinea1->cantidad = 1;
if (!$newLinea1->save()) {
    // No se pudo guardar la línea
}

// Añadimos una línea de texto
$newLinea2 = $factura->getNewLine();
$newLinea2->descripcion = 'Mano de obra';
$newLinea2->cantidad = 5;
$newLinea2->pvpunitario = 10;
if (!$newLinea2->save()) {
    // No se pudo guardar la línea
}

// Actualizamos los totales de la factura
$lineas = $factura->getLines();
if (!Calculator::calculate($factura, $lineas, true)) {
    // No se pudieron actualizar los totales
}
```

## 📦 Actualización del Stock

El stock se actualiza automáticamente al guardar cualquier documento de compra o venta. A continuación, se detalla cómo se gestionan las cantidades:

- **Albaranes y facturas de compra:** Las cantidades se **suman** al stock de los productos.
- **Albaranes y facturas de venta:** Las cantidades se **restan** del stock de los productos.
- **Pedidos de compra:** Las cantidades se registran como **pendiente de recepción**.
- **Pedidos de venta:** Las cantidades se anotan como **reservadas**.

Este comportamiento se define en los [estados del documento](https://facturascripts.com/publicaciones/estados-del-documento-680), los cuales pueden configurarse desde el panel de control.

## 🔓 Estados y editable

Los [estados del documento](https://facturascripts.com/publicaciones/estados-del-documento-680) también definen si el documento es o no **editable**. No se puede modificar el campo editable de forma independiente, ya que al guardar volverá a poner el editable como lo defina el estado (campo idestado).

### 📝 Cambiar el estado

Podemos cambiar el estado cambiando el valor del campo `idestado` del modelo. Y para consultar los estados disponibles podemos usar el método `getAvailableStatus()`.
