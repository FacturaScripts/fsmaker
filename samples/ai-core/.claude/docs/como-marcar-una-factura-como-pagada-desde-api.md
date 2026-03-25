# Cómo marcar una factura como pagada desde API

> **ID:** 2283 | **Permalink:** como-marcar-una-factura-como-pagada-desde-api | **Última modificación:** 09-10-2025
> **URL oficial:** https://facturascripts.com/como-marcar-una-factura-como-pagada-desde-api

Para marcar una factura como pagada desde API debemos usar los endpoints:

- `pagarFacturaCliente`
- `pagarFacturaProveedor`

Y enviar la fecha de pago, el codigo de la forma de pago y el campo pagada:

- `fechapago`: fecha del pago
- `codpago`: código de la forma de pago
- `pagada`: bool (0/1)

## Ejemplo
Para marcar como pagada la factura de cliente con id 1234, haríamos una petición **POST** a `htts://donde-este-fs-instalado/api/3/pagarFacturaCliente/1234` con los siguientes campos:

- `fechapago`: 2025-10-10
- `codpago`: TRANSF
- `pagada`: 1
