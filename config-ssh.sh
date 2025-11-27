#!/bin/bash/

#Update de chave privada
rm -f ~/.ssh/id_rsa
rm -f ~/.ssh/id_rsa.pub

echo "Apenas clique enter, gerando chaves privadas"

ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa

cp -r ~/.ssh/id_rsa.pub ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh

echo "Copie sua chave privada, e salve no windows sem nenhuma extensao, e para conectar coloque ssh -i caminho-da-chave-pv user@ip"

cat ~/.ssh/id_rsa
