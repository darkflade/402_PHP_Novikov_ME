install:
	composer install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@mkdir -p database
	@touch database/database.sqlite
	php artisan key:generate --force
	php artisan migrate --force
