#!/bin/bash

# Název kontejneru
CONTAINER_NAME="tda"

# Zkontroluj, zda kontejner s tímto jménem již běží
if [ "$(docker ps -q -f name=${CONTAINER_NAME})" ]; then
    echo "Kontejner s názvem ${CONTAINER_NAME} již běží."
    exit 1
fi

docker rm ${CONTAINER_NAME}

# Sestav a spusť nový kontejner
docker build -t ${CONTAINER_NAME} .
docker run -d --name ${CONTAINER_NAME} -p 8080:80 ${CONTAINER_NAME}

echo "Kontejner s názvem ${CONTAINER_NAME} byl úspěšně spuštěn."
