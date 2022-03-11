# WiredBeauty Hackaton project

## Synopsis

This project is an answer to WiredBeauty's call of tenders.

## Installation

```bash
cp .env.test .env && cd symfony/ && cp .env.test .env
docker-compose build --pull --no-cache
docker-compose up -d
```

go to http://localhost:8080/
connect to db with credentials in docker-compose.yml
import wordpress.sql

on php-fpm docker:
```bash
composer update 
npm install
php bin/console d:d:c
php bin/console d:s:u --force
```

```
# URL
http://127.0.0.1

# Env DB (à mettre dans .env, si pas déjà présent)
DATABASE_URL="mysql://root:root@db:3306/symfony?serverVersion=8.0"
```

