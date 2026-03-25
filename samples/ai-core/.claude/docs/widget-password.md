# Widget Password (campo contraseña)

> **ID:** 657 | **Permalink:** widget-password-421 | **Última modificación:** 10-11-2025
> **URL oficial:** https://facturascripts.com/widget-password-421

En los archivos XMLView puedes usar el widget password (WidgetPassword) para mostrar y editar contraseñas en formularios. Su comportamiento es idéntico al del [widget de texto](https://facturascripts.com/publicaciones/widget-text-96), salvo que muestra puntos en lugar de los caracteres de la contraseña.

```xml
<column name="new-password" numcolumns="4" order="100">
	<widget type="password" fieldname="newPassword" />
</column>
```

## ⚙️ Configuración

A continuación tienes las propiedades más útiles del widget y cómo usarlas. Usa siempre `fieldname` para enlazar con el campo del modelo.

- **fieldname** (obligatorio): nombre del campo que contiene la contraseña. Ejemplo: `fieldname="newPassword"`.
- **required**: si lo pones (`required="true"`) impide guardar el formulario cuando el campo está vacío.
- **icon**: nombre del icono que se mostrará dentro del campo. Revisa los [iconos disponibles](https://facturascripts.com/publicaciones/iconos-disponibles-308). Ejemplo: `icon="fa-solid fa-lock"`.
- **maxlength**: longitud máxima permitida para la contraseña. Ejemplo: `maxlength="32"`.

## 🧩 Ejemplos prácticos

Ejemplo básico:

```xml
<widget type="password" fieldname="userPassword" />
```

Ejemplo con validación obligatoria y límite de longitud:

```xml
<widget type="password" fieldname="newPassword" required="true" maxlength="64" />
```

Ejemplo con icono visible en el campo:

```xml
<widget type="password" fieldname="apiKey" icon="fa-solid fa-key" />
```

## 👁️ Mostrar/ocultar contraseña

El widget incluye un icono de ojo que permite alternar la visibilidad de la contraseña: si haces clic en el ojo verás los caracteres; vuelve a hacer clic para ocultarlos como puntos. Esta funcionalidad es puramente visual y no cambia cómo se guarda el dato.

![widget password edicion](https://i.imgur.com/yR8JKM3.png)

## 🔐 Seguridad y buenas prácticas

- El widget solo controla la presentación en la interfaz; asegúrate de validar y proteger la contraseña en el servidor (hash, sal, etc.).
- No dependas únicamente del atributo `maxlength` para la seguridad; también valida en el backend.
- Usa `required` cuando la contraseña sea obligatoria y muestra mensajes claros al usuario sobre requisitos (longitud mínima, caracteres especiales, etc.).
