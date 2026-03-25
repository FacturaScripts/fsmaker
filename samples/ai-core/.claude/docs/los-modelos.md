# Los modelos

> **ID:** 618 | **Permalink:** los-modelos-228 | **Última modificación:** 18-09-2025
> **URL oficial:** https://facturascripts.com/los-modelos-228

Los modelos en FacturaScripts son clases que representan las tablas de la base de datos y proporcionan una interfaz orientada a objetos para interactuar con los datos. Utilizan el patrón Active Record y están basados en una arquitectura de clase abstracta (`ModelClass`) con un trait (`ModelTrait`) que implementa la funcionalidad básica.

Un modelo es una clase que debe ir en un archivo **con el mismo nombre** y dentro de la **carpeta Model** del plugin.

## Ejemplo: Project.php
Siguiendo con el ejemplo del plugin MyNewPlugin, vamos a añadirle un modelo llamado **Project** para dar de alta proyectos.

```
<?php
namespace FacturaScripts\Plugins\MyNewPlugin\Model;

use FacturaScripts\Core\Template\ModelClass;
use FacturaScripts\Core\Template\ModelTrait;
use FacturaScripts\Core\Tools;

class Project extends ModelClass
{
    use ModelTrait;

    public $active;
    public $creation_date;
    public $id;
    public $name;
		
    public function clear(): void
    {
        parent::clear();
        $this->active = true;
        $this->creation_date = Tools::dateTime();
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'projects';
    }
}
```

El nombre del modelo debe ser siempre en singular y el de la tabla en plural. Si necesita un modelo para edificios, el modelo se debería llamar **Edificio** y la tabla **edificios**.

### Nombres de columna conflictivos
- Evita usar en sus columnas nombres como: **action** y **code**.
- Evita usar nombres con mayúsculas.

### namespace
Es importante recordar que nuestro plugin utiliza el espacio de nombres *FacturaScripts\Plugins\MyNewPlugin*, porque el plugin se llama *MyNewPlugin*. Si cambiamos este espacio de nombres dejará de funcionar. **El espacio de nombres se debe corresponder con la carpeta donde está el archivo**.

### tableName()
Debe devolver el **nombre de la tabla** de la base de datos que utiliza este modelo para leer y guardar los registros. FacturaScripts buscará a continuación el [archivo XML con la definición de la estructura de la tabla](https://facturascripts.com/publicaciones/la-definicion-de-la-estructura-de-la-tabla-514), en la carpeta **Table**. Y usará este archivo para crear o comprobar la estructura de la tabla.

#### Creación de la tabla
No es necesario que crees manualmente la tabla del modelo, ni que ejecutes ningún SQL. Simplemente debes tener el archivo XML en la carpeta Table. FacturaScripts se encargará de generar la tabla automáticamente. Si además quieres rellenar la tabla con registros predefinidos consulta el [método install() del modelo](https://facturascripts.com/publicaciones/install-205).

### primaryColumn()
Esta función debe devolver el **nombre de la columna** de la clave primaria de la tabla. Lo habitual es usar el nombre `id`, pero puedes usar otro nombre. Cuando se busque un registro con este modelo, se buscará por esta columna. Por ejemplo, los siguientes métodos del modelo buscan por ese campo:

- find()
- [get()](https://facturascripts.com/publicaciones/get-695)
- load()
- [loadFromCode()](https://facturascripts.com/publicaciones/loadfromcode-677)

## Ejemplos de uso
Los modelos ofrecen una gran variedad de funciones para operar con los registros. A continuación tienes las más habituales:

### Crear un registro
Para crear nuevos registros del modelo podemos hacer un `new NOMBRE_MODELO()`, asignar los valores y después llamar al método `save()`, o bien usar el método `create()`:

```
// Opción 1: Crear objeto y guardar
$proyecto = new Proyecto();
$proyecto->name = 'Proyecto 1';
$proyecto->active = true;

if ($proyecto->save()) {
   echo "Proyecto guardado correctamente. ID " . $proyecto->id();
}

// Opción 2: Crear e insertar directamente
$proyecto = Proyecto::create([
   'name' => 'Proyecto 1',
   'active' => true
]);
```

### Buscar registros
Para buscar registros concretos tenemos los métodos `find()`, `findWhere()` y `findWhereEq()`. Para buscar muchos registros tenemos el método `all()`:

```
// Buscar por clave primaria
$proyecto = Proyecto::find(123);
if ($proyecto) {
   echo $proyecto->name;
}

// Buscar por campo específico
$proyecto = Proyecto::findWhereEq('name', 'Proyecto 1');

// Buscar por condiciones
$where = [
   Where::eq('name', 'Proyecto 1'),
   Where::eq('active', true),
];
$proyecto = Proyecto::findWhere($where);

// Obtener todos los registros
$proyectos = Proyecto::all();

// Obtener con filtros y orden
$where = [
   Where::eq('active', true),
];
$order = ['nombre' => 'ASC'];
$proyectosActivos = Proyecto::all(Where, $order, 0, 10); // offset 0, limit 10
```

### Cargar registros
Una vez instanciado el modelo podemos usar los métodos `load()`, `loadWhere()` y `loadWhereEq()` para cargar los datos de un registro concreto:

```
// Cargar por clave primaria
$proyecto = new Proyecto();
if ($proyecto->load(123)) {
   echo $proyecto->name;
}

// Cargar por campo específico
$proyecto = new Proyecto();
if ($proyecto->loadWhereEq('name', 'Proyecto 1')) {
   echo $proyecto->name;
}

// Cargar por condiciones
$proyecto = new Proyecto();
$where = [
   Where::eq('name', 'Proyecto 1'),
   Where::eq('active', true),
];
if ($proyecto->loadWhere($where)) {
   echo $proyecto->name;
}
```

### Actualizar registros
Para actuializar en la base de datos la información de un registro podemos usar los métodos `save()`, `update()` o `updateOrCreate()`:

```
// Cargar y modificar
$proyecto = Proyecto::find(123);
if ($proyecto) {
   $proyecto->active = false;
   $proyecto->save();
}

// Actualizar directamente
$proyecto = Proyecto::find(123);
$proyecto->update([
   'active' => false
]);

// Actualizar o crear
$proyecto = Proyecto::updateOrCreate([
   'active' => false
], [
   'name' => 'Proyecto 1',
   'active' => false
]);
```

### Eliminar registros
Para eliminar registros de la base de datos tenemos los métodos `delete()` y `deleteWhere()`:

```
// Eliminar un registro específico
$proyecto = Proyecto::find(123);
if ($proyecto) {
   $proyecto->delete();
}

// Eliminar por condiciones
Proyecto::deleteWhere([
   Where::eq('activo', false),
   Where::lt('creation_date', '2020-01-01 00:00:00')
]);
```
