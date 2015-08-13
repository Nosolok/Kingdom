#!/bin/sh

command -v docker >/dev/null 2>&1 || { echo "Докер не установлен. Установка: http://docs.docker.com/linux/started"; exit 1; }
command -v curl >/dev/null 2>&1 || { echo "Curl не установлен. Установка: sudo apt-get install curl"; exit 1; }

case $1 in
'')
    $0 start
;;
'download')
    echo "Обновление образа из Docker hub ..."
	docker pull rottenwood/kingdom
;;
'build')
    $0 stop
    echo "Сборка Docker-образа ..."
    docker rmi rottenwood/kingdom > /dev/null 2>&1
    docker build --no-cache -t rottenwood/kingdom .
;;
'start')
    $0 stop
    $0 download

    echo "Настройка контейнера данных для MySQL ..."
    docker create --name kingdom-mysql-data rottenwood/mysql-data > /dev/null 2>&1

    echo "Создание нового контейнера для сервера MySQL ..."
    docker run -d --name kingdom-mysql-server --volumes-from=kingdom-mysql-data -e MYSQL_PASS="docker" -e MYSQL_USER="kingdom" tutum/mysql > /dev/null 2>&1

    echo "Создание нового контейнера ..."
    docker run -d --name="kingdom" -v $(pwd):/kingdom --entrypoint="kingdom/app/docker/init.sh" --link kingdom-mysql-server:mysql -p 7777:7777 -p 81:81 rottenwood/kingdom > /dev/null 2>&1

    echo "Контейнер создан!"
    echo "Игра доступна по адресу: \033[1;33;24mhttp://localhost:81\033[0m"
;;
'stop')
    echo "Удаление старого контейнера ..."
    docker kill kingdom > /dev/null 2>&1
    docker rm kingdom > /dev/null 2>&1
    docker kill kingdom-mysql-server > /dev/null 2>&1
    docker rm kingdom-mysql-server > /dev/null 2>&1
    echo "Контейнер остановлен и удален!"
;;
*)
	echo "Применение:"
	echo "\033[1;33;24m$0\033[0m - запуск контейнера с окружением"
	echo "\033[1;33;24m$0 start\033[0m - запуск контейнера с окружением"
	echo "\033[1;33;24m$0 stop\033[0m - остановка контейнера"
	echo "\033[1;33;24m$0 new\033[0m - сборка нового образа"
;;
esac
