# fsmaker
  Developers tool for FacturaScripts
  https://facturascripts.com/publicaciones/fsmaker-y-el-nuevo-curso-de-programacion

# download
  https://github.com/FacturaScripts/fsmaker/archive/refs/heads/main.zip 

# Instalar fsmaker en Linux o macOS
  wget https://raw.githubusercontent.com/FacturaScripts/docker-facturascripts/master/fsmaker.php
  wget https://raw.githubusercontent.com/FacturaScripts/docker-facturascripts/master/fsmaker.sh
  chmod +x fsmaker.sh
  sudo mv fsmaker.* /usr/local/bin
  sudo mv /usr/local/bin/fsmaker.sh /usr/local/bin/fsmaker

# Instalar fsmaker en Windows
  - Añadir a variable de entorno de windows PATH (del usuario o del sistema) el path/ruta donde esté instalado PHP
  - Añadir a vatiable de entorno de windows PATH (del usuario o del sistema) el path/ruta donde esté instalado fsmaker 
  - Modificar fsmaker.bat para ...
    + Cambiar el path de la variable pathPHP por el path/ruta donde esté instalado PHP
    + Cambiar el path de la variable pathFSMAKER por el path/ruta donde esté instalado fsmaker

# carpeta Samples
  En esta carpeta hay ficheros con terminación .sample que son las plantillas para generación de controladores, el fichero Init.php, El fichero Cron.php, etc.
  Esta carpeta, junto con su contenido, tiene que estar dentro del directorio donde se encuentre fsmaker.php


## Issues / Feedback
https://facturascripts.com/contacto