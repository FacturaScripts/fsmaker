# La definición de la estructura de la tabla

> **ID:** 619 | **Permalink:** la-definicion-de-la-estructura-de-la-tabla-514 | **Última modificación:** 23-02-2026
> **URL oficial:** https://facturascripts.com/la-definicion-de-la-estructura-de-la-tabla-514

FacturaScripts utiliza archivos XML para definir la estructura de las tablas de la base de datos. El núcleo del sistema se encarga de revisar estas tablas para:

- Crear la tabla si no existe.
- Verificar que la tabla tenga todas las columnas necesarias y crearlas en caso de faltar alguna.
- No realizar ninguna acción si la tabla contiene columnas adicionales a las definidas en el XML.

Los archivos XML deben ubicarse en la carpeta **Table** de cada plugin, y cada archivo debe nombrarse exactamente igual que la tabla correspondiente.

## Ejemplo: projects.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<table>
    <column>
        <name>name</name>
        <type>character varying(100)</type>
        <null>NO</null>
    </column>
    <column>
        <name>codproject</name>
        <type>character varying(8)</type>
        <null>NO</null>
    </column>
    <constraint>
        <name>projects_pkey</name>
        <type>PRIMARY KEY (codproject)</type>
    </constraint>
</table>
```

Cada etiqueta **column** representa una columna de la tabla y puede incluir las siguientes subetiquetas:

- **name**: Nombre de la columna. Se recomienda usar solo minúsculas para evitar conflictos.
- **type**: Tipo de dato que almacenará la columna.
- **null**: Indica si la columna admite valores nulos: `YES` para admitir, `NO` para no admitir. Si esta etiqueta se omite, se asume `YES`.
- **default**: Valor por defecto a asignar cuando no se proporciona uno, especialmente útil al agregar nuevas columnas a tablas con registros preexistentes. Recuerda que para la operación diaria, los valores por defecto deben implementarse en el método `clear()` del modelo, no en el XML.
- **rename**: Permite indicar el nombre anterior de una columna para que el actualizador la renombre en lugar de crearla de cero. Resulta clave cuando cambias el identificador de un campo en tablas que ya existen en instalaciones activas.

### Renombrado de Columnas Existentes

Si necesitas cambiar el nombre de una columna sin perder la información existente, añade la etiqueta **rename** dentro de la columna nueva:

```xml
<column>
    <name>notas</name>
    <type>text</type>
    <rename>observaciones</rename>
</column>
```

En este ejemplo, FacturaScripts detecta que `notas` debe sustituir a `observaciones` y ejecuta el renombrado durante la actualización de la tabla. Asegúrate de mantener el mismo tipo de datos o actualizarlo para evitar incompatibilidades.

### Nombres Reservados

Evita usar los siguientes nombres para columnas, ya que están reservados:

- action
- activetab
- code

### Tipos de Datos Soportados

FacturaScripts fue originalmente desarrollado para PostgreSQL, por lo que la mayoría de los nombres de tipos de datos corresponden a este sistema. Algunos ejemplos son:

- **serial**: Entero autoincrementable, recomendado para claves primarias numéricas.
- **integer**: Entero convencional.
- **double precision**: Números con decimales.
- **boolean**: Valores lógicos: `true` o `false`.
- **character varying(100)**: Texto de longitud variable, hasta 100 caracteres. Puedes modificar el número para otros rangos.
- **text**: Texto extensible (hasta 4000 caracteres).
- **date**: Fechas.
- **time**: Horas.
- **timestamp**: Fecha y hora.

### Definición de Restricciones: Clave Primaria, Claves Foráneas, etc.

Las restricciones, como claves primarias y foráneas, se definen usando etiquetas **constraint**. Cada restricción debe tener un nombre único, lo que permite al sistema verificar su existencia en la tabla:

- Si la restricción no se encuentra, se crea.
- Si ya existe, no se realiza ninguna acción.
- Si se cambia el nombre de la restricción, se elimina la existente y se crea una nueva.

#### Ejemplo de Clave Foránea

```xml
<constraint>
    <name>ca_albaranesprov_series</name>
    <type>FOREIGN KEY (codserie) REFERENCES series (codserie) ON DELETE RESTRICT ON UPDATE CASCADE</type>
</constraint>
```

En este ejemplo se define que:

- La columna `codserie` de la tabla actual se relaciona con la columna `codserie` de la tabla `series`.
- Se restringe la eliminación en la tabla `series` si existe una dependencia.
- Las actualizaciones en la tabla `series` se propagan a la tabla actual.

#### Ejemplo de Restricción Única

```xml
<constraint>
    <name>uniq_codigo_albaranesprov</name>
    <type>UNIQUE (codigo, idempresa)</type>
</constraint>
```

Este ejemplo asegura que la combinación de `codigo` e `idempresa` sea única en la tabla.

### Definición de Índices

Además de las restricciones, puedes declarar índices adicionales usando la etiqueta **index**. Cada índice debe incluir un nombre (sin el prefijo `fs_`, ya que el sistema lo añadirá automáticamente) y las columnas afectadas, separadas por comas si son varias.

```xml
<index>
    <name>ventas_fecha_idx</name>
    <columns>fechaalta, idempresa</columns>
</index>
```

Al reconstruir, FacturaScripts comprobará qué índices existen en la base de datos y creará, actualizará o eliminará los necesarios para ajustarse al XML.

### ¿No ves los cambios en la base de datos?

Durante el desarrollo puede ocurrir que la tabla no se cree o que los cambios realizados en el XML no se reflejen en la base de datos. Para solucionarlo, sigue estos pasos:

1. Ve al menú **Administrador → Plugins** y pulsa el botón **Reconstruir**.
2. Instancia el modelo correspondiente (por ejemplo, `new FacturaCliente()`).

Recuerda que FacturaScripts crea las tablas que no existen al instanciar el modelo. Además, los cambios en el XML no se aplican hasta que se borre la caché, ya que el sistema utiliza una caché para evitar comprobaciones constantes. Para forzar la aplicación de las modificaciones, es necesaria la reconstrucción mencionada.
