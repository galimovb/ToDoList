#!/bin/bash

while getopts t: flag
do
    case "${flag}" in
        t) TAG=${OPTARG};;
    esac
done

if [ -z "$TAG" ]; then
    echo "Использование: ./start.sh -t <tag>"
    exit 1
fi

export TAG=$TAG
docker-compose up -d
