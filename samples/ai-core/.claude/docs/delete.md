# $modelo-&gt;delete() - Método para eliminar registros

> **ID:** 624 | **Permalink:** delete-986 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/delete-986

El método `delete()` del modelo se utiliza para eliminar un registro de la base de datos. Este método devuelve `true` si el registro se ha eliminado con éxito, o `false` en caso de que ocurra algún error.

## Ejemplo: Eliminar un registro específico
Supongamos que queremos eliminar el proyecto llamado 'test':

```php
$project = new Project();
if ($project->load('test')) {
    // Registro encontrado, procedemos a eliminarlo.
    $project->delete();
    // Registro eliminado.
}
```

### Ejemplo: Eliminar varios registros
Supongamos que queremos eliminar todos los productos que están bloqueados:

```php
$where = [Where::eq('bloqueado', true)];
foreach(Producto::all($where) as $producto) {
	$producto->delete();
}
```

En este caso, primero llamado al [método `all()` del modelo](https://facturascripts.com/publicaciones/all-863) para obtener todos los productos bloqueados. Después, recorremos el conjunto resultante con un bucle `foreach` y llamamos al método `delete()` para eliminar cada registro.

### deleteWhere($where)

Desde la versión 2025 podemos llamar directamente al método `deleteWhere()` para eliminar múltiples registros con una sola llamada.

```php
$where = [Where::eq('bloqueado', true)];
Producto::deleteWhere($where);
```

En este ejemplo eliminamos todos los productos bloqueados.
