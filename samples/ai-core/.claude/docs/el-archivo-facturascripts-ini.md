# El archivo facturascripts.ini

> **ID:** 613 | **Permalink:** el-archivo-facturascripts-ini-37 | **Última modificación:** 13-08-2025
> **URL oficial:** https://facturascripts.com/el-archivo-facturascripts-ini-37

El archivo `facturascripts.ini` es imprescindible para cada plugin, ya que define información clave sobre el mismo. A continuación, se describen los campos que debe contener y las recomendaciones para su correcta utilización.

## Campos Obligatorios

- **name**: Indica el nombre del plugin. _Debe coincidir exactamente con el nombre del directorio del plugin_.
- **description**: Proporciona una descripción breve pero completa del plugin.
- **version**: Especifica la versión del plugin. _Debe ser un número entero o decimal_.
  - **Ejemplo correcto:** `version = 1.0`
  - **Ejemplo incorrecto:** `version = 1.0.1` (no se acepta formato triple)
  - **Ejemplo incorrecto:** `version = 1.0-beta` (se debe utilizar formato decimal)
- **min_version**: Establece la **versión mínima de FacturaScripts** necesaria para el funcionamiento del plugin.
  - **Ejemplo:** `min_version = 2025`

## Campos Opcionales

- **min_php**: Define la **versión mínima de PHP** requerida. _Debe usarse un formato decimal_.
  - **Ejemplo correcto:** `min_php = 8.2`
  - **Ejemplo incorrecto:** `min_php = 8.4.5`

- **require**: Lista los **plugins requeridos** para que el plugin funcione correctamente. Si son varios, deben estar separados por comas sin espacios.
  - **Ejemplo correcto:** `require = 'POS'`
  - **Ejemplo incorrecto:** `require = 'POS, Servicios'` (no se deben incluir espacios)
  - **Ejemplo correcto:** `require = 'POS,Servicios'`

- **require_php**: Especifica las **extensiones de PHP necesarias**, separadas por comas sin espacios.
  - **Ejemplo correcto:** `require_php = 'soap'`
  - **Ejemplo incorrecto:** `require_php = 'soap, imap'` (no se deben incluir espacios)
  - **Ejemplo correcto:** `require_php = 'soap,imap'`

- **compatible**: Indica los **plugins compatibles**. La lista debe escribirse sin espacios después de las comas.
  - **Ejemplo correcto:** `compatible = 'POS'`
  - **Ejemplo incorrecto:** `compatible = 'POS, Servicios'`
  - **Ejemplo correcto:** `compatible = 'POS,Servicios'`

## Ejemplo de facturascripts.ini

A continuación se muestra un ejemplo básico del archivo:

```
name = 'MyNewPlugin'
description = 'My fantastic new plugin for FacturaScripts'
version = 1
min_version = 2025
```

Este ejemplo indica que se trata del plugin **MyNewPlugin**, en su versión **1**, y que requiere **FacturaScripts 2025** o una versión superior para su funcionamiento.

## Detalle del Campo: name

El campo **name** indica el nombre del plugin y _debe coincidir exactamente_ con el nombre del directorio en el que se encuentra el plugin. Errores comunes incluyen:

- Errores tipográficos en el nombre del archivo `facturascripts.ini` (por ejemplo, añadir o faltar una 's').
- Inconsistencias entre el nombre del directorio y el valor del campo `name`.

### Ejemplos de Casos Correctos e Incorrectos

- **Correcto:**
  - Carpeta: `Miplugin`
  - Configuración: `name = 'Miplugin'`

- **Incorrecto:**
  - Carpeta: `Mi plugin` y configuración: `name = 'Miplugin'` (no se deben usar espacios en el nombre de la carpeta).
  - Carpeta: `Miplugin` y configuración: `name = 'MiPlugin'` (la 'p' debe estar en minúscula para mantener la coherencia).
