#!/bin/bash

# 🚀 Script de instalación en Ubuntu Server 22.04

set -e  # salir si ocurre un error

echo "=== Actualizando sistema (excepto PHP) ==="
# Bloquear paquetes PHP para que no se actualicen antes de tiempo
sudo apt-mark hold php*

sudo apt update -y
sudo apt upgrade -y

echo "=== Instalando MySQL Server ==="
sudo apt install -y mysql-server

echo "=== Instalando y configurando SSH ==="
sudo apt install -y openssh-server
# Generar claves SSH sin passphrase para root (si no existen)
if [ ! -f ~/.ssh/id_rsa ]; then
    ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa -N ""
fi

echo "=== Instalando Apache2 ==="
sudo apt install -y apache2

echo "=== Instalando PHP 8.3 ==="
# Repositorio oficial de PHP
sudo apt install -y software-properties-common ca-certificates lsb-release apt-transport-https
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update -y
sudo apt install -y php8.3 libapache2-mod-php8.3 php8.3-mysql

echo "=== Creando usuario 'cosmopolita' ==="
# Crear usuario sin password
if id "cosmopolita" &>/dev/null; then
    echo "Usuario cosmopolita ya existe"
else
    sudo adduser --disabled-password --gecos "" cosmopolita
fi

echo "=== Dando permisos de administración a 'cosmopolita' ==="
# Sudo sin contraseña
echo "cosmopolita ALL=(ALL) NOPASSWD:ALL" | sudo tee /etc/sudoers.d/cosmopolita

echo "=== Configurando permisos para app web ==="
# Dar acceso al usuario a la carpeta web
sudo chown -R cosmopolita:www-data /var/www/html
sudo chmod -R 775 /var/www/html

echo "=== Habilitando Apache y PHP ==="
sudo systemctl enable apache2
sudo systemctl restart apache2

echo "=== Configurando Crontab ==="
sudo apt-get update -y
sudo apt-get install -y cron
sudo systemctl enable cron
sudo systemctl start cron

# Asegurar permisos de ejecución del script
sudo chmod +x /home/enzo/registro/spawn.sh

# Agregar la tarea al crontab
(sudo crontab -u enzo -l 2>/dev/null; echo "* * * * * /home/enzo/registro/spawn.sh") | sudo crontab -u enzo -

echo "=== Servidor listo ==="
echo "Usuario: cosmopolita (sin contraseña, con sudo)"
echo "Directorio web: /var/www/html (editable por cosmopolita)"



