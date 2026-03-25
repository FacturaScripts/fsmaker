# Método url() del modelo

> **ID:** 633 | **Permalink:** url-898 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/url-898

El método url() del modelo nos devuelve la url para editar, crear o listar registros, según el tipo que solicitemos.

## $modelo->url(string $type = 'auto', string $list = 'List'): string
El **parámetro $type** admite los valores auto, edit, new y list, y sirve para indicar qué url queremos:
- **list**: nos devolverá la url para listar este modelo. Si es el modelo Producto, nos devolverá ListProducto, que es el controlador que muestra el listado de productos.
- **new**: nos devuelve la url donde podemos crear un nuevo elemento de este modelo. Si es el modelo Familia, nos devolverá EditFamilia, que es el controlador que nos permite crear y editar familias.
- **edit**: nos devuelve la url donde podemos editar este modelo. Siguiendo el ejemplo anterior también es EditFamilia.
- **auto**: (opción por defecto) esta opción comprueba si el objeto tiene datos para decidir si mostrar el resultado de list o el de edit. Siguiendo con el ejemplo de Familia, si el modelo tiene cargados los datos de una familia, devolverá EditFamilia, en caso contrario devolverá ListFamilia.

El **parámetro $list** sirve para modificar fácilmente el comportamiento de la opción list.

## Valores por defecto
El método url() entiende que todo modelo tendrá un controlador "**List + nombre del modelo**" (ListProducto, ListFamilia, ListCliente...) para listar, y que también tendrá un controlador "**Edit + nombre del modelo**" (EditProducto, EditFamilia, EditCliente...) para crear o editar,

### No tengo un controlador List, está en la pestaña de otro
En algunas ocasiones no tenemos un controlador List para nuestro modelo, sino que añadimos una pestaña en otro controlador List relacionado, como por ejemplo el modelo Retencion, que no tiene un controlador ListRetencion, sino que se lista en una pestaña del controlador ListImpuesto. En estos casos debemos modificar la función url cambiando el valor del parámetro $list:
```
public function url(string $type = 'auto', string $list = 'ListImpuesto?activetab=List'): string
{
   return parent::url($type, $list);
}
```
Como vemos, hemos modificado el valor de **$list** a '**ListImpuesto?activetab=List**' ¿Por qué? Porque así cuando la función url() vaya a construir la url del listado, le sumará el nombre del modelo (Retencion) y nos devolverá 'ListImpuesto?activetab=ListRetencion', que se corresponde con el controlador ListImpuesto y la pestaña ListRetencion, que es realmente donde está el listado de retenciones.

### No tengo un controlador List, está en la pestaña de un Edit
Existen otros casos donde no tenemos un listado general, sino que el listado está en un EditController de otro modelo. Veamos este, por ejemplo:
```
<?php
namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Service_itinerary extends Base\ModelClass
{
   use Base\ModelTrait;

   public $idservice;
   ....

   // devolvemos el modelo Servicio relacionado (mediante $idservicio)
   public function getServicio() {
      $servicio = new Service();
      $servicio->loadFromCode($this->idservice);
      return $servicio;
   }

   public function url(string $type = 'auto', string $list = 'List'): string {
      if ($type == 'list') { 
         return $this->getServicio()->url();
      }

      // funcionamiento normal para el resto de opciones
      return parent::url($type, $list);
   }
}
```
Cuando nos solicitan la url de listado, buscamos el servicio relacionado y devolvemos su url.
