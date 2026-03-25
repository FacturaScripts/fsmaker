# Cómo subir archivos usando la API de FacturaScripts

> **ID:** 2161 | **Permalink:** como-subir-un-archivo-usando-la-api-de-facturascripts | **Última modificación:** 09-07-2025
> **URL oficial:** https://facturascripts.com/como-subir-un-archivo-usando-la-api-de-facturascripts

FacturaScripts permite la subida de archivos mediante el uso de su API. Para ello, se debe realizar una petición al endpoint `uploadFiles`.

### Cómo hacer la petición al endpoint

Para hacer la petición, se debe enviar una solicitud **POST** al siguiente endpoint:

```
POST /api/3/uploadFiles
```

En el cuerpo (body) de la petición, se debe incluir el parámetro `files[]` junto al archivo que se desea subir. **FacturaScripts no permite subir archivos con extensión .php.**

**Ejemplo de petición al endpoint de la API con Insomnia**

![Imagen de petición al endpoint de la API con Insomnia](https://i.imgur.com/2LRfCQC.png)

**Ejemplo en PHP:**

```
$ch = curl_init();
$body = [
    'files[]' => new CURLFile('/ruta/a/tuArchivo/imagen1.jpg'),
]
$headers = [
    'Token:' . 'TuToken',
];
curl_setopt($ch, CURLOPT_URL, 'http://TuURL/api/3/uploadFiles');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$response = curl_exec($ch);
```

**Ejemplo en JS:**

```
const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

async function subirArchivo() {
  const form = new FormData();

  form.append('files[]', fs.createReadStream(/ruta/a/tuArchivo/imagen1.jpg), {
    filename: 'img1.jpg',
    contentType: 'image/jpeg'
  });

  const headers = {
    'Token': 'TuToken',
    ...form.getHeaders(),
  };

  const response = await axios.post('http://TuURL/api/3/uploadFiles', form, { headers });
}

subirArchivo();
```

Si el archivo se sube correctamente, la API devolverá la información sobre el archivo o archivos subidos. Además, el nombre del archivo se modificará automáticamente en la carpeta `MyFiles` para evitar que se sobrescriba.

En caso de error, la API devolverá un mensaje de error o un array `files[]` vacío.

![Array files vacío por error en la peticion](https://i.imgur.com/Uqeazwn.png)

---

Para más detalles puedes consultar el archivo ApiUploadFiles.php en GitHub:

[facturascripts/Core/Controller/ApiUploadFiles.php at master · NeoRazorX/facturascripts · GitHub](https://github.com/NeoRazorX/facturascripts/blob/master/Core/Controller/ApiUploadFiles.php)
