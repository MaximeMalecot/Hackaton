## Getting started

```bash
docker-compose build --pull --no-cache
docker-compose up -d
```

on php docker:
```bash
php bin/console d:s:u --force
```

```
# URL
http://127.0.0.1

# Env DB (à mettre dans .env, si pas déjà présent)
DATABASE_URL="postgresql://postgres:password@db:5432/db?serverVersion=13&charset=utf8"
```

