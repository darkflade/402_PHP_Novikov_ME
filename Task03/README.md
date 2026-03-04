# Task03: Prime Game SPA (Slim 4.15.1)

Single Page Application for the game "Is the number prime?" with backend REST API on Slim and SQLite.

## Requirements
- PHP 8+
- Composer

## Install
From `Task03` directory:

```bash
composer install
```

## Run
From `Task03` directory:

```bash
php -S localhost:3000 -t public
```

Open:
- `http://localhost:3000/`
- `http://localhost:3000/index.html`

Database file is created in `Task03/db/game.sqlite`.

## API
- `GET /games` - all games
- `GET /games/{id}` - one game with steps
- `POST /games` - create game, JSON body: `{"player_name":"Alex"}`
- `POST /step/{id}` - add step, JSON body: `{"number":17,"user_answer":"yes"}`
