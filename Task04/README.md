Single Page Application для игры "Простое ли число?".
Backend создан с помощью Laravel и хранит данные в SQLite.

## Требования
- PHP 8+
- Composer
- Make (для авто установки на Linux)

## Установка
Из `Task04`:

```bash
make install
```

Если `make` недоступен, тогда:

```bash
composer install
cp .env.example .env
mkdir -p database
: > database/database.sqlite
php artisan key:generate --force
php artisan migrate --force
```

## Запуска
From `Task04` directory:

```bash
php artisan serve
```

Open:
- `http://localhost:8000/`

## Database
SQLite file location:
- `Task04/database/database.sqlite`

## API
- `GET /games` - list all games
- `GET /games/{id}` - game with all steps
- `POST /games` - create game, JSON body: `{"player_name":"Alex"}`
- `POST /step/{id}` - add step, JSON body: `{"number":17,"user_answer":"yes"}`

Error responses:
- `404` with `{"error":"Game not found"}` for unknown game id
- `400` for invalid request payloads
