# Métodos id(), primaryColumnValue() y changeId() del modelo

> **ID:** 637 | **Permalink:** primarycolumnvalue-328 | **Última modificación:** 10-01-2026
> **URL oficial:** https://facturascripts.com/primarycolumnvalue-328

El método `id()` devuelve el valor de la columna primaria del modelo, que se define mediante el método [primaryColumn()](https://facturascripts.com/publicaciones/primarycolumn-492). Ese valor único identifica un registro en la base de datos y es imprescindible para operaciones de actualización y eliminación.

Ten en cuenta:

- Si el registro aún no existe en la base de datos (por ejemplo, un modelo nuevo sin guardar), `id()` puede no tener valor (null o vacío) hasta que se inserte.
- Usa `id()` siempre que necesites acceder de forma directa a la clave primaria de un registro gestionado por FacturaScripts.

## 🧩 Ejemplos de uso

Obtener el id de un modelo y mostrarlo:

```php
echo $modelo->id();
```

Comprobar si un modelo tiene id y actuar en consecuencia:

```php
if ($modelo->id()) {
    // Registro ya existente
    echo "El id es: " . $modelo->id();
} else {
    // Registro nuevo, aún sin insertar en BD
    echo "El registro no tiene id asignado.";
}
```

## 🔁 primaryColumnValue() (compatibilidad)

Antes existía el método `primaryColumnValue()`. Actualmente sigue estando disponible por compatibilidad, pero su uso está desaconsejado. Reemplázalo por `id()` en código nuevo:

```php
// Antiguo (desaconsejado)
echo $modelo->primaryColumnValue();

// Recomendado
echo $modelo->id();
```

## ✏️ changeId()

Si necesitas cambiar el valor de la clave primaria, puedes usar `changeId($nuevoValor)`. El método devuelve `true` si el cambio se realizó correctamente y `false` en caso contrario.

```php
if ($modelo->changeId(1234)) {
    echo "Id cambiado a 1234";
} else {
    echo "No se pudo cambiar el id";
}
```

Al usar `changeId()` ten en cuenta:

- Normalmente se aplica a registros ya guardados en la base de datos. Cambiar la clave primaria de un registro es una operación delicada.
- Si en los [XML de las tablas](https://facturascripts.com/publicaciones/la-definicion-de-la-estructura-de-la-tabla-514) relacionadas has declarado claves ajenas y la base de datos está configurada con restricciones de integridad referencial (por ejemplo `ON UPDATE CASCADE`), la propia base de datos puede replicar los cambios del id a las tablas relacionadas. Asegúrate de conocer cómo están definidas las relaciones antes de proceder.

## ✅ Buenas prácticas

- Prefiere `id()` para obtener la clave primaria en tu código.
- Evita cambiar la clave primaria salvo que sea realmente necesario.
- Antes de ejecutar `changeId()`, verifica las restricciones y relaciones (XML y configuración de la BD) para no romper la integridad referencial.
- Haz copias de seguridad antes de cambios masivos en claves primarias.
