# fsmaker
Herramienta de creación de plugin para FacturaScripts.
- https://facturascripts.com/fsmaker

## Instalar con composer
Si ya tiene instalado PHP y Composer, puede instalar fsmaker con este comando:

```
composer global require facturascripts/fsmaker
```

## Ejecutar
Una vez instalado con composer puede ejecutarlo desde cualquier directorio:

```
composer global exec fsmaker
```

### Comando corto con Linux / Mac
Si está usando Linux o macOS, puede añadir fsmaker directamente al path para
ejecutarlo sin necesidad de llamar a composer:

```
sudo ln -s ~/.config/composer/vendor/bin/fsmaker /usr/local/bin/fsmaker
```

Ahora ya puede llamar simplemente a fsmaker:

```
fsmaker
```

## Issues / Feedback
https://facturascripts.com/contacto