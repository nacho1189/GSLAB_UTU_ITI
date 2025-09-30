# Setup del Servidor Apache y PHP para SGL_ITI

---

## Requisitos

 - Ubuntu 22.04 LTS o cualquier otra distro de Debian (mismo debian)
 - Acceso `sudo`

---

## 1. Instalacion de Apache y PHP

```bash

sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql -y

```
## Levantar apache y verificar que corre correctamente

```
sudo systemctl start apache2
sudo systemctl status apache2

```
## Crear un archivo de prueba para verificar que PHP funcione correctamente

```
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php

```
Solo tenemos que ir al buscador y escribir http://localhost/info.php

## Configuracion del proyecto 

Para poder trabajar de una forma organizada y modular es preferible tener el
pryecto clonado en un directorio local por fuera de apache.

```
sudo mkdir /var/www/html/SGL_ITI
sudo cp -r ~/ruta/del/proyecto/* /var/www/html/SGL_ITI

```
El primer comando crea la carpeta de nuestro proyecto en el direcotrio de
apache. Y el segundo copia nuestro proyecto clonado en alguna parte del home
a la ruta de apache.

##Asignar los permisos correctos

```
sudo chown -R www-data:www-data /var/www/html/SGL_ITI
sudo chmod -R 755 /var/www/html/SGL_ITI

```
El comando `chown` le otorga el dominio de nuestro proyecto al usuario y grupo
"www-data" quedense con que es un usuario que apache utiliza para manipular
la informacion de nuestra web correctamente.

El comando `chmod` por otro lado otorga permisos de lectura, escritura y 
ejecucion recursivamente a todos los archivos dentro del directorio de 
nuestro proyecto

### Activar los modulos y reiniciar apache2

```
sudo a2enmod rewrite
sudo systemctl restart apache2

```

### Instalar algunas extensiones para PHP

```
sudo apt install php-curl php-xml php-mbtring php-zip -y

```

## Recordatorios

### Borrar el archivo info.php que creamos

```
sudo rm ~/var/www/html/info.php

```
Si ya estamos sobre la ruta solo ponemos

```
sudo rm info.php

```
Fue solo un archivo de prueba no lo vamos a necesitar a futuro 


