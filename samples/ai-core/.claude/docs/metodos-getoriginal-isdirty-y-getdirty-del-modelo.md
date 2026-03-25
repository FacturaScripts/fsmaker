# Métodos getOriginal(), isDirty() y getDirty() del modelo

> **ID:** 2434 | **Permalink:** metodos-getoriginal-isdirty-y-getdirty-del-modelo | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/metodos-getoriginal-isdirty-y-getdirty-del-modelo

Los modelos de FacturaScripts incluyen métodos para **rastrear cambios en los datos**: `getOriginal()`, `isDirty()` y `getDirty()`. Estos métodos son útiles para detectar qué campos han sido modificados antes de guardar.

## getOriginal()
Devuelve el valor original de un campo tal como fue cargado desde la base de datos.

- Sintaxis
	- public function getOriginal(string $field): mixed
- Parámetros:
	- $field - Nombre del campo del que se quiere obtener el valor original
- Retorno
	- Devuelve el valor original del campo, o null si el campo no existe.

### Ejemplo
```
  $agency = new AgenciaTransporte();
  $agency->loadFromCode('MRW');

  echo $agency->nombre; // "MRW Express"

  // Modificamos el nombre
  $agency->nombre = 'MRW Internacional';

  echo $agency->nombre; // "MRW Internacional"
  echo $agency->getOriginal('nombre'); // "MRW Express"

  // Después de guardar, getOriginal() devuelve el nuevo valor
  $agency->save();
  echo $agency->getOriginal('nombre'); // "MRW Internacional"
```

## isDirty()
Verifica si el modelo o un campo específico ha sido modificado desde que fue cargado.

- Sintaxis
	- public function isDirty(?string $field = null): bool
- Parámetros
	- $field (opcional) - Nombre del campo específico a verificar. Si no se especifica, verifica si hay algún cambio en el modelo.
- Retorno
	- Devuelve true si hay cambios, false si no los hay.

### Ejemplos
```
  $agency = new AgenciaTransporte();
  $agency->loadFromCode('MRW');

  // Verificar si el modelo tiene cambios
  echo $agency->isDirty(); // false (recién cargado)

  // Modificar un campo
  $agency->nombre = 'Nuevo nombre';

  // Verificar cambios globales
  echo $agency->isDirty(); // true

  // Verificar campos específicos
  echo $agency->isDirty('nombre'); // true
  echo $agency->isDirty('telefono'); // false

  // Restaurar al valor original
  $agency->nombre = $agency->getOriginal('nombre');
  echo $agency->isDirty('nombre'); // false

  // Después de guardar, ya no está dirty
  $agency->nombre = 'Otro nombre';
  $agency->save();
  echo $agency->isDirty(); // false
```

## getDirty()
Devuelve un array asociativo con todos los campos que han sido modificados y sus valores actuales.

- Sintaxis
	- public function getDirty(): array
- Retorno
	- Devuelve un array donde las claves son los nombres de los campos modificados y los valores son los valores actuales de esos campos.

### Ejemplo
```
  $agency = new AgenciaTransporte();
  $agency->loadFromCode('MRW');

  // Sin cambios
  print_r($agency->getDirty()); // []

  // Modificar varios campos
  $agency->nombre = 'MRW Internacional';
  $agency->telefono = '+34 912 345 678';

  // Obtener todos los cambios
  $cambios = $agency->getDirty();
  print_r($cambios);
  /*
  Array
  (
      [nombre] => MRW Internacional
      [telefono] => +34 912 345 678
  )
  */

  // Restaurar un campo al original
  $agency->nombre = $agency->getOriginal('nombre');
  $cambios = $agency->getDirty();
  print_r($cambios);
  /*
  Array
  (
      [telefono] => +34 912 345 678
  )
  */
```

## Casos de uso comunes
Detectar cambios antes de guardar

```
  if ($model->isDirty()) {
      // Hay cambios pendientes
      $model->save();
      $this->addMessage('Cambios guardados correctamente');
  } else {
      $this->addWarning('No hay cambios que guardar');
  }
```

## Auditoría de cambios
```
  $cambios = $model->getDirty();
  foreach ($cambios as $campo => $valorNuevo) {
      $valorAntiguo = $model->getOriginal($campo);
      $this->log(
          "Campo '$campo' cambió de '$valorAntiguo' a '$valorNuevo'"
      );
  }
```

## Validar solo campos modificados
```
  if ($model->isDirty('email')) {
      // Solo validar el email si ha cambiado
      if (!filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
          $this->addError('Email no válido');
          return false;
      }
  }
```

## Mostrar advertencias al usuario
```
  if ($model->isDirty('precio') && $model->precio > $model->getOriginal('precio')) {
      $this->addWarning('¡Atención! Estás aumentando el precio');
  }
```
