#!/bin/bash

# Variáveis
c="clear"
i="sudo apt-get install -y"
p="sudo pip3 install"
b="--break-system-packages"
w="sudo wget"
co="sudo cp -r"
rmf="sudo rm -rf"
token="Coloque o token aqui"

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


# baixar ferramentas de uso pessoal
$i gccgo-go && $i golang-go
$i apksigner && $i apktool
$i zipalign && $i php-curl
$i tor && $i seclists
pipx install uro && sudo pipx install uro
$c

# Baixando ferramentas bug bounty
git clone https://github.com/s0md3v/XSStrike.git && cd XSStrike
sudo chmod +x * && sudo mv * /usr/loca/bin/
sudo go install github.com/tomnomnom/waybackurls@latest && sudo go install github.com/tomnomnom/gf@latest
sudo CGO_ENABLED=1 go install github.com/projectdiscovery/katana/cmd/katana@latest && sudo go install -v github.com/projectdiscovery/httpx/cmd/httpx@latest
$c

# Customizando terminal
sudo git clone https://github.com/cesarbtakeda/MyBash-Zshrc.git && cd MyBash-Zshrc
sudo cp -r zshrc.sh ~/.zshrc && cp -r zshrc.sh ~/.zshrc

#Baixando beef e ferramentas de phishing
git clone https://github.com/cesarbtakeda/H00ks_T0x1na.git && cd H00ks_T0x1na/API-BEEF
git clone https://github.com/beefproject/beef.git && cd beef
sudo chmod 777 * && sudo ./install
sudo bundle install && sudo chmod 777 *
$co ../config.yaml config.yaml


# Atualização final e limpeza do sistema
sudo apt-get update -y && sudo apt-get full-upgrade -y
sudo apt-get install kali-linux-everything -y && sudo apt-get autoremove -y
$c

sudo subfinder -up
sudo wpscan --update
sudo nuclei -ut
$c

echo "[*A atualização foi concluída com sucesso*]"



# configurando pastas das ferramentas baixadas
sudo mv ~/go/bin/httpx ~/go/bin/httpx-pd
$co ~/go/bin/*  /usr/local/bin/

#Ngrok baixando
curl -sSL https://ngrok-agent.s3.amazonaws.com/ngrok.asc \
  | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null \
  && echo "deb https://ngrok-agent.s3.amazonaws.com bookworm main" \
  | sudo tee /etc/apt/sources.list.d/ngrok.list \
  && sudo apt update \
  && sudo apt install ngrok -y
echo "Ngrok baixado"

#Config ngrok
sudo ngrok config add-authtoken $token
ngrok config add-authtoken $token


# inicializando todos os servicos necessarios
sudo systemctl start ssh && sudo systemctl enable ssh.service
sudo systemctl start tor && sudo systemctl enable tor.service
sudo systemctl start postgresql && sudo systemctl enable postgresql.service
sudo systemctl start apache2 && sudo systemctl enable apache2.service
sudo msfdb init && sudo msfdb start
