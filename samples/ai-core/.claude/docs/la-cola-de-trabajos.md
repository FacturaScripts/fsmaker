# La cola de trabajos

> **ID:** 1601 | **Permalink:** la-cola-de-trabajos | **Última modificación:** 17-03-2026
> **URL oficial:** https://facturascripts.com/la-cola-de-trabajos

En ocasiones queremos ejecutar procesos en "segundo plano", por ejemplo, actualizar el número de productos de una familia cuando se añade un producto. Este proceso no es fundamental, es decir, no necesitamos ese contador actualizado al momento de añadir el producto. Para este tipo de procesos usamos la cola de trabajos.

La cola de trabajos se ejecuta al final de cada ejecución. Es decir, cada vez que abrimos una página (como por ejemplo un producto o el listado de clientes), al final del proceso se ejecuta la cola de trabajos. Por tanto estos trabajos en "segundo plano" se ejecutan al final y de uno en uno. Por eso se usa para procesos no fundamentales.

## Eventos y workers
La cola de trabajos se compone de eventos y workers. Cada vez que se añade un producto, cliente, etc, se lanza un evento. Por otro lado tenemos los workers, que son clases PHP registradas para "escuchar" estos eventos y procesar.

### Eventos de los modelos
Todos los modelos lanzan automáticamente eventos cuando se añade, modifica o elimina contenido. Por ejemplo:

- Cuando añadimos un producto se lanzan los eventos ``Model.Producto.Insert`` y ``Model.Producto.Save``
- Cuando modificamos un producto se lanza el evento ``Model.Producto.Update`` y ``Model.Producto.Save``
- Cuando eliminamos un producto se lanza el evento ``Model.Producto.Delete``

Para otros modelos se lanzan los mismos eventos, pero con el nombre del modelo en cuestión.

### Lanzar eventos personalizados
Podemos lanzar un evento en cualquier momento llamando a ``WorkQueue::send()``.

```
// lanzamos el evento 'test-event' con el valor 'test-value'
WorkQueue::send('test-event', 'test-value');
```

En la [versión 2024.94](https://facturascripts.com/publicaciones/facturascripts-2024-94-lista-de-cambios) se añadió la opción de lanzar eventos para ejecutar pasados unos segundos llamando a ``WorkQueue::sendFuture()``.

```
// lanzamos el evento 'test-event' para que se procese en 300 segundos (5 minutos)
WorkQueue::sendFuture(300, 'test-event', 'test-value');
```

## Workers
Los workers son clases PHP que se encuentran en la **carpeta Worker** y que podemos registrar en la cola de trabajos para procesar ciertos eventos. Este sería un ejemplo básico de worker:

```
namespace FacturaScripts\Plugins\MiPlugin\Worker;

use FacturaScripts\Core\Model\WorkEvent;
use FacturaScripts\Core\Template\WorkerClass;

class MiWorker extends WorkerClass
{
	public function run(WorkEvent $event): bool
	{
		// tu código aquí
		
		return $this->done();
	}
}
```

### Registrar un worker
Para hacer que un worker "escuche" un evento podemos llamar a ``WorkQueue::addWorker()`` en la función ``init()`` del [archivo Init.php](/publicaciones/el-archivo-init-php-307) de nuestro plugin. En el siguiente ejemplo registraremos el worker MiWorker para que escuche el evento de cuando se modifica un producto.

```
WorkQueue::addWorker('MiWorker', 'Model.Producto.Update');
```

Con esto conseguimos que nuestro worker se ejecute si se modifica algún producto. Podemos registrar un worker para muchos eventos, simplemente hay que registrarlo varias veces:

```
WorkQueue::addWorker('MiWorker', 'Model.Producto.Insert');
WorkQueue::addWorker('MiWorker', 'Model.Producto.Delete');

// esta es otra opción
WorkQueue::addWorker('MiWorker', 'Model.Producto.*');
```

Incluso podemos hacer que escuche todos los eventos:

```
WorkQueue::addWorker('MiWorker', '*');
```

#### Eventos en el Init::update()
El método ``Init::update()`` de los plugins se ejecuta antes del ``Init::init()``, por lo que si lanzas algún evento en el update, debes añadir los workers necesarios también en el update().

### Evitar bucles de eventos
En ocasiones queremos ejecutar un trabajo cuando se modifica un producto, y este trabajo modifica a su vez el producto, por lo que se crea un nuevo evento que desencadena un bucle infinito. Para evitar este problema, podemos desactivar la generación de ese evento en el propio worker llamando al método ``preventNewEvents()``:

```
namespace FacturaScripts\Plugins\MiPlugin\Worker;

use FacturaScripts\Core\Model\WorkEvent;
use FacturaScripts\Core\Template\WorkerClass;

class MiWorker extends WorkerClass
{
	public function run(WorkEvent $event): bool
	{
		// evitamos que se creen nuevos eventos de tipo Model.Producto.Save
		$this->preventNewEvents(['Model.Producto.Save']);
		
		// tu código aquí
		
		return $this->done();
	}
}
```

### El parámetro WorkEvent $event
Cuando un worker se ejecuta, recibe como parámetro un objeto de tipo `WorkEvent` que contiene información sobre el evento. Este objeto tiene dos propiedades importantes para acceder a los datos:

- `$event->value`: Contiene el valor principal del evento (normalmente el ID del registro)
- `$event->params()`: Devuelve un array con parámetros adicionales

También existe `$event->param('clave', 'valorPorDefecto')` para obtener un parámetro concreto con valor por defecto opcional.

#### Eventos de modelos (Insert, Update, Save, Delete)
Cuando se produce un evento automático de modelo, el sistema envía estos datos:

- `$event->value`: El ID del registro (resultado de `$model->id()`)
- `$event->params()`: Array con todos los campos del modelo (resultado de `$model->toArray()`)

Por ejemplo, cuando insertamos un producto con referencia "PROD-001" y precio 10.50:

```
// el evento se lanza automáticamente así:
WorkQueue::send(
    'Model.Producto.Insert',
    '5',                    // ID del producto insertado
    [                       // todos los campos del producto
        'idproducto' => 5,
        'referencia' => 'PROD-001',
        'precio' => 10.50,
        'descripcion' => 'Mi producto',
        // ... resto de campos
    ]
);
```

En nuestro worker podemos acceder a todos estos datos:

```
public function run(WorkEvent $event): bool
{
    // obtenemos el ID
    $idproducto = $event->value; // "5"
    
    // obtenemos todos los datos
    $datos = $event->params(); // array con todos los campos
    
    // accedemos a campos específicos
    $referencia = $event->param('referencia'); // "PROD-001"
    $precio = $event->param('precio'); // 10.50
    
    return $this->done();
}
```

**Importante**: Los eventos **Insert**, **Save** y **Delete** siempre envían el array completo del modelo. Sin embargo, cuando usamos el método ``$model->update(array $values)`` directamente (en lugar de modificar campos y hacer ``save()``), el evento **Update** sólo enviará los campos que se están actualizando, no todos los campos del modelo.

#### Eventos personalizados
Cuando lanzamos eventos personalizados con ``WorkQueue::send()``, nosotros decidimos qué enviamos en el value y en los params. Por ejemplo:

```
// lanzamos un evento personalizado cuando una factura se paga
WorkQueue::send(
    'Model.FacturaCliente.Paid',
    $factura->idfactura,    // el ID de la factura
    $factura->toArray()     // todos los datos de la factura
);
```

O podemos enviar sólo los datos que necesitemos:

```
// evento con parámetros personalizados
WorkQueue::send(
    'proceso-especial',
    'identificador',
    [
        'dato1' => 'valor1',
        'dato2' => 'valor2'
    ]
);
```

En resumen: 
- **Eventos de modelos**: ``$event->value`` es el ID y ``$event->params()`` contiene todos los campos del modelo (excepto en algunos casos de Update)
- **Eventos personalizados**: ``$event->value`` y ``$event->params()`` contienen lo que nosotros decidamos al lanzar el evento

### Ver la cola de trabajos
Podemos ver la lista de eventos a procesar desde el **menú administrador, logs**. En la pestaña de **eventos de trabajos**.

![eventos de la cola de trabajos](https://i.imgur.com/cs06SIg.png)
