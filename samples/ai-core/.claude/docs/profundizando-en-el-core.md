# Profundizando en el CORE

> **ID:** 1598 | **Permalink:** profundizando-en-el-core | **Última modificación:** 08-12-2025
> **URL oficial:** https://facturascripts.com/profundizando-en-el-core

FacturaScripts es también un framework PHP, por lo que también implementa los mismos conceptos que otros frameworks:

- [Enrutado](/publicaciones/enrutado-el-sistema-de-rutas)
- [Gestión de errores](/publicaciones/gestion-de-errores)
- [Gestión de plugins](/publicaciones/gestion-de-plugins)
- [Logs](/publicaciones/mostrar-mensajes-errores-y-alertas)
- [Caché](/publicaciones/uso-de-la-cache)
- [Acceso a base de datos](/publicaciones/acceso-a-la-base-de-datos-818)
- [Colas de trabajo](/publicaciones/la-cola-de-trabajos)

## El Kernel
El kernel de FacturaScripts se encarga tanto del enrutado como de la gestión de errores, además de proporcionar funciones para comprobar tiempos de ejecución.

### Versión de FacturaScripts
Podemos obtener la versión de FacturaScripts que estamos ejecutando llamando a la función ``Kernel::version()``.

```
use FacturaScripts\Core\Kernel;

echo Kernel::version();
```

### Obtener el tiempo de ejecución
Podemos obtener el tiempo de ejecución llamando a la función ``Kernel::getExecutionTime()``. Este función nos devuelve el tiempo total, en segundos, con 5 decimales.

```
echo Kernel::getExecutionTime(); // 0.00123
```

### Calcular un tiempo de ejecución
Si queremos saber cuanto tardamos en ejecutar una determinada función o bloque de código, podemos usar un temporizador. Para ello debemos iniciarlo llamando a la función ``Kernel::startTimer()`` y luego detenerlo llamando a ``Kernel::stopTimer()``.

```
// iniciamos un temporizador
Kernel::startTimer('mi-prueba');

// tu código aquí

// detenemos el temporizador
$total = Kernel::stopTimer('mi-prueba');
echo $total; // este es el tiempo de ejecución, en segundos

// también podemos obtener el tiempo llamando a getTimer()
echo Kernel::getTimer('mi-prueba');
```

Si tenemos activado el [modo debug](https://facturascripts.com/publicaciones/creacion-de-plugins-210#md_h6), podremos ver los temporizadores en la primera sección de la **barra de debug**.

![barra debug](https://i.imgur.com/MSECHqv.png)
