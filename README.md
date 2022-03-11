# WiredBeauty Hackaton project

## Synopsis

This project is an answer to WiredBeauty's call of tenders.

## Installation

```bash
cp .env.test .env && cd symfony/ && cp .env.test .env
docker-compose build --pull --no-cache
docker-compose up -d
```

go to [adminer](http://localhost:8080/) and connect to db with credentials in docker-compose.yml

import wordpress.sql

on php-fpm docker:
```bash
composer update 
npm install
php bin/console d:d:c
php bin/console d:s:u --force
```
Front solution can now be found [here](http://127.0.0.1:8888)

And back one [here](http://127.0.0.1)

