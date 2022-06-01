# fsmaker
Herramienta de creación de plugin para FacturaScripts.
- https://facturascripts.com/publicaciones/fsmaker-y-el-nuevo-curso-de-programacion

# Instalar en Linux o macOS
Ejecute estos comandos en el terminal del sistema (necesita tener git instalado en el sistema).

```
git clone https://github.com/FacturaScripts/fsmaker.git
sudo ln -s $(pwd)/fsmaker/fsmaker.sh /usr/local/bin/fsmaker
sudo chmod +x /usr/local/bin/fsmaker
```

# Instalar en Windows
- Instale PHP, si todavía no lo ha hecho.
- Descargue desde https://github.com/FacturaScripts/fsmaker/archive/refs/heads/main.zip 
- Añada la variable de entorno de windows PATH (del usuario o del sistema) el path/ruta donde esté instalado PHP
- Añada la variable de entorno de windows PATH (del usuario o del sistema) el path/ruta donde esté instalado fsmaker 
- Modifique fsmaker.bat para ...
  + Cambie el path de la variable pathPHP por el path/ruta donde esté instalado PHP
  + Cambie el path de la variable pathFSMAKER por el path/ruta donde esté instalado fsmaker

## Issues / Feedback
https://facturascripts.com/contacto