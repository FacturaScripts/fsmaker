# Enviar emails con NewMail

> **ID:** 920 | **Permalink:** envio-de-emails-211 | **Última modificación:** 21-10-2025
> **URL oficial:** https://facturascripts.com/envio-de-emails-211

Podemos enviar emails desde FacturaScripts utilizando la clase [NewMail](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Email-NewMail.html). Este clase facilita el envío de emails desde FacturaScripts. Utiliza los datos del correo configurado en el menú administrador, emails.

```
use FacturaScripts\Dinamic\Lib\Email\NewMail;

$mail = NewMail::create()
	->to('pepe@gmail.com', 'Pepe')
	->subject('Hola Pepe')
	->body('Hola Pepe, esto es una prueba');

if ($mail->send()) {
	// email enviado correctamente
}
```

## 📎 Añadir un archivo adjunto
Usaremos el método ``addAttachment()`` de la clase NewMail para añadir archivos adjuntos al email:

```
$mail = NewMail::create()
	->to('pepe@gmail.com', 'Pepe')
	->subject('Hola Pepe')
	->body('Hola Pepe, esto es una prueba')
	->addAttachment('el-archivo.pdf', 'Nombre del archivo para el cliente.pdf');

if ($mail->send()) {
	// email enviado correctamente
}
```

## ✉️ Enviar con copia
El campo CC en los emails significa "con copia". Se utiliza para enviar una copia de un correo electrónico a otras personas además del destinatario principal. Las personas que se incluyen en el campo CC reciben una copia del mensaje, pero no se consideran destinatarios principales.

El campo CC se puede utilizar para varios propósitos, entre los que se incluyen:
- Mantener a otros informados de un correo electrónico. Por ejemplo, si envías un correo electrónico a un cliente, puedes incluir a tu gerente en el campo CC para que esté al tanto de la conversación.
- Obtener comentarios de otras personas. Si estás trabajando en un proyecto, puedes enviar un correo electrónico a tus compañeros de equipo en el campo CC para obtener su opinión.
- Remitir un correo electrónico a otras personas. Si recibes un correo electrónico que crees que puede ser útil para otras personas, puedes reenviarlo en el campo CC.

```
$mail = NewMail::create()
	->to('pepe@gmail.com', 'Pepe')
	->cc('jose@gmail.com', 'Jose')
	->cc('antonio@gmail.com', 'Antonio')
	->subject('Hola')
	->body('Hola, esto es una prueba');

if ($mail->send()) {
	// email enviado correctamente
}
```

### 👁️‍🗨️ Enviar con copia oculta
El campo BCC, que significa "con copia oculta", se utiliza para enviar una copia de un correo electrónico a otras personas sin que los demás destinatarios puedan ver sus direcciones de correo electrónico.

El campo BCC se puede utilizar para varios propósitos, entre los que se incluyen:
- Proteger la privacidad de las direcciones de correo electrónico. Por ejemplo, si estás enviando un correo electrónico a un grupo de personas, puedes utilizar el campo BCC para ocultar las direcciones de correo electrónico de los demás destinatarios.
- Enviar un correo electrónico a un grupo grande de personas sin abrumar a los destinatarios principales. Si estás enviando un correo electrónico a un grupo grande de personas, puedes utilizar el campo BCC para evitar que los destinatarios principales reciban una respuesta de todos los demás destinatarios.
- Enviar un correo electrónico a personas que no se conocen entre sí. Si estás enviando un correo electrónico a personas que no se conocen entre sí, puedes utilizar el campo BCC para evitar que conozcan las direcciones de correo electrónico de los demás.

```
$mail = NewMail::create()
	->bcc('jose@gmail.com', 'Jose')
	->bcc('antonio@gmail.com', 'Antonio')
	->subject('Hola')
	->body('Hola, esto es una prueba');

if ($mail->send()) {
	// email enviado correctamente
}
```

## 📫 Notificaciones
En ocasiones debemos mandar el mismo tipo de email muchas veces. Para estos casos, en lugar de escribir todo el texto cada vez, podemos preparar una notificación con el texto precargado (que además podrá modificar el usuario).

### 📝 Cómo crear una notificación
Para crear la notificación usaremos el modelo MailNotification:

```
$notificationModel->name = 'mi-notificacion';
$notificationModel->subject = 'mi-titulo';
$notificationModel->body = 'mi-texto';
$notificationModel->enabled = true;
$notificationModel->save();
```

Podemos usar cadenas de texto a reemplazar, como ``{name}``, que será reemplazado por el nombre del contacto o cliente al que enviemos el email.

### 📨 Cómo enviar un notificación de email
Para enviar la notificación simplemente debemos llamar a la clase [MailNotifier](https://doc.facturascripts.com/classes/FacturaScripts-Core-Lib-Email-MailNotifier.html):

```
MailNotifier::send('mi-notificacion', $email, $name);
```

Si hemos incluído otras cadenas de texto a reemplazar en el email, por ejemplo una fecha de vencimiento y un nombre de proyecto, podemos incluir esos valores a reemplazar en los parámetros.

```
// Si el texto de la notificación es "Hola {name}, la fecha de vencimiento del proyecto {project} es {expiration}"
// Podemos enviar la notificación así

MailNotifier::send('mi-notificacion', $email, $name, [
	'project' => 'Proyecto 123',
	'expiration' => '11-12-2024'
]);
```

### 📝 Textos predeterminados para emails
Cuando el usuario envía por email una factura, albarán, etc ... Tenemos unos [textos predeterminados](https://facturascripts.com/publicaciones/cambiar-los-textos-de-las-plantillas-de-emails) para esos emails, que realmente son notificaciones: `sendmail-AlbaranCliente`, `sendmail-FacturaCliente` ... puedes conseguir el mismo comportamiento con tus modelos simplemente creando una notificación para cada uno con el prefijo `sendmail-`.
