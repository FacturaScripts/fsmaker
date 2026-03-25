# Método get() del modelo

> **ID:** 626 | **Permalink:** get-695 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/get-695

El método **get()** de los modelos **devuelve una instancia del mismo tipo** con los datos del registro solicitado.

## Ejemplo: Obtener la familia 1234
```php
$modelFamilia = new Familia();
$familia = $modelFamilia->get('1234');
```

## Otros métodos similares
Los modelos tienen otros modelos similares para obtener un registro:

### find()
El método `find()` es mejor porque ni siquiera tienes que inicializar el objeto, es un método estático que puedes llamar directamente:

```php
$familia = Familia::find('1234');
```

### load()
Si desea cargar los valores en el modelo actual, es preferible utilizar la función [load()](/publicaciones/load). Esta función carga los datos del registro en el objeto actual, en lugar de devolver uno nuevo.

Existen otras variantes de load() que conviene conocer:
- loadWhere()
- loadWhereEq()
