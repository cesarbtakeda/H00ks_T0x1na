#!/bin/bash

# Variáveis
c="clear"
i="sudo apt-get install -y"
p="sudo pip3 install"
b="--break-system-packages"  # Corrigido o nome da opção

echo "[*Atualizando arquivos...*]"
sudo apt-get update -y 
echo "[*Atualização completa*]"
$c

# Instalação de pacotes básicos
$i python3-pip
$i php-curl
$i python2
$i python3
$i wget
$i apache2


# Instalando Cloudflared 
echo "[*Baixando Cloudflared*]"
wget https://github.com/cloudflare/cloudflared/releases/download/2025.1.0/cloudflared-fips-linux-amd64 -O cloudflare
chmod +x cloudflare
sudo mv cloudflare /usr/local/bin/
echo "[*Cloudflared baixado e instalado com sucesso!!*]"


#baixar ferramentas de uso pessoal
$i gccgo-go && $i golang-go
$i apksigner && $i apktool
$i zipalign && $i php-curl
$i tor && $i seclists
$c

#Baixando ferramentas bug bounty
git clone https://github.com/s0md3v/XSStrike.git && cd XSStrike
sudo chmod +x * && sudo mv * /usr/loca/bin/
go install github.com/tomnomnom/waybackurls@latest && go install github.com/tomnomnom/gf@latest
CGO_ENABLED=1 go install github.com/projectdiscovery/katana/cmd/katana@latest && go install -v github.com/projectdiscovery/httpx/cmd/httpx@latest 
$c

#Customizando terminal
sudo git clone https://github.com/cesarbtakeda/MyBash-Zshrc.git && cd MyBash-Zshrc
sudo cp -r zshrc.sh ~/.zshrc && cp -r zshrc.sh ~/.zshrc

#Baixando beef e ferramentas de phishing
git clone https://github.com/cesarbtakeda/H00ks_T0x1na.git && cd H00ks_T0x1na/API-BEEF 
git clone https://github.com/beefproject/beef.git && cd beef
sudo chmod 777 * && sudo ./install   
sudo bundle install && sudo chmod 777 *
sudo cp -r ../config.yaml config.yaml


# Atualização final e limpeza do sistema
sudo apt-get update -y && sudo apt-get full-upgrade -y
sudo apt-get install kali-linux-everything -y && sudo apt-get autoremove -y
$c



echo "[*A atualização foi concluída com sucesso*]"
