#!/bin/bash

# Variáveis
c="clear"
i="sudo apt-get install -y"
p="sudo pip3 install"
b="--break-system-package"


echo "[**Atualizando arquivos...**]"
sudo apt-get update -y
echo "[**Atualização completa**] "
$c

# Instalação de pacotes
$i python3-pip
$i php-curl
$i python2
$i python3
$i wget
$i systemctl
$i apache2


# Instalando Cloudflare
echo "[**Baixando CloudFlare**]"
wget https://github.com/cloudflare/cloudflared/releases/download/2025.1.0/cloudflared-fips-linux-amd64 -O cloudflared
chmod +x cloudflared
sudo mv cloudflared /usr/local/bin/
echo "[**CloudFlare baixado!!**]"


# Atualização final e limpeza do sistema
sudo apt-get update -y && sudo apt-get full-upgrade -y
sudo apt-get autoremove -y
$c

echo "[**A atualização foi concluída com sucesso**]"
