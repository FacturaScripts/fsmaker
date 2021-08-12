@echo off 

rem ... creamos las variables que usaremos como path para llamar a php y a fsmaker.php
set pathPHP=C:\xampp\php
set pathFSMAKER=E:\PROGRAMACION\PHP\FACTURASCRIPTS\fsmaker

rem ... creamos la variable de entorno donde está este .bat para en futuras ocasiones poder llamar a este .bat sin necesidad de poner su path
rem ... setx path "%path%;%pathFSMAKER%" ... no funciona bien ... no se queda guardado como variable de entorno después de ejecutar el .bat

%pathPHP%\php %pathFSMAKER%\fsmaker.php %1
