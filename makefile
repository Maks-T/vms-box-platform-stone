# Variables
COMPOSE = docker compose
# Path to artisan inside the container
ARTISAN = php /var/www/html/artisan

# Main Docker commands

# Build and start containers
up:
	$(COMPOSE) up -d --build

# Stop containers
down:
	$(COMPOSE) down

# Open a bash shell in the PHP container
bash:
	$(COMPOSE) exec app bash

# --- Laravel commands ---

# Run database migrations
migrate:
	$(COMPOSE) exec app $(ARTISAN) migrate

# Fresh migrations with seeders
mfs:
	$(COMPOSE) exec app $(ARTISAN) migrate:fresh --seed

# Clear all Laravel cache
pacc:
	$(COMPOSE) exec app $(ARTISAN) cache:clear
	$(COMPOSE) exec app $(ARTISAN) config:clear
	$(COMPOSE) exec app $(ARTISAN) route:clear

# Clear Horizon queues
hc:
	$(COMPOSE) exec app $(ARTISAN) horizon:clear redis --queue=default
	$(COMPOSE) exec app $(ARTISAN) horizon:clear redis --queue=ms-fast
	$(COMPOSE) exec app $(ARTISAN) horizon:clear redis --queue=ms-long

# --- Database and cache utilities ---

# Flush Redis database
rf:
	$(COMPOSE) exec redis redis-cli FLUSHDB

# --- Package and frontend management ---

# Install Composer dependencies
ci:
	$(COMPOSE) exec app composer install

# Install npm dependencies
ni:
	$(COMPOSE) exec app npm install

# Start Vite development server
dev:
	$(COMPOSE) exec app npm run dev

# Build frontend assets for production
build:
	$(COMPOSE) exec app npm run build

# --- System commands ---

# Fix file ownership (useful on Linux/WSL)
chown:
	sudo chown -R ${USER}:${USER} .

# Generate a file tree (excluding temporary and vendor directories)
tree:
	@echo "Generating file tree..."
	tree -I 'vendor|node_modules|.git|storage|bootstrap/cache' > tree.txt
	@echo "Tree saved to tree.txt"

# Delete temporary combine context files
cc:
	@echo "Deleting temporary files..."
	@find . \( -name "*_combine*" -o -name "tree.txt" -o -name "*Zone.Identifier" \) -type f -delete
	@echo "Done!"

uw: update-widget

update-widget:
	@echo "Updating CPQ Stone widget..."
	rm -rf public/cpq-stone
	git clone --branch deploy/build --single-branch git@github.com:kapitulin24/cpq-stone-calc.git public/cpq-stone
	rm -rf public/cpq-stone/.git
	@echo "Widget updated successfully!"

DATE ?= 2026-05-03
EXCLUDE ?=

git-diff:
	chmod +x generate_diffs.sh
	./generate_diffs.sh $(DATE) "$(EXCLUDE)"

# Запуск всех тестов платформы с генерацией отчета покрытия
test:
	$(COMPOSE) exec app env XDEBUG_MODE=coverage $(ARTISAN) test --coverage-html=public/coverage

# Запуск тестов ядра NicoleCore с генерацией отчета покрытия
test-nicole:
	$(COMPOSE) exec app env XDEBUG_MODE=coverage $(ARTISAN) test --testsuite=NicoleCore --coverage-html=public/coverage

# Запуск тестов плагина камня ValerieStone с генерацией отчета покрытия
test-valerie:
	$(COMPOSE) exec app env XDEBUG_MODE=coverage $(ARTISAN) test --testsuite=ValerieStone --coverage-html=public/coverage

package-update:
	$(COMPOSE) exec app composer update nicole/box-core valerie/box-industry-stone

# Help
help:
	@echo "Available commands for VMS platform:"
	@echo "  make up           - Start Docker containers"
	@echo "  make down         - Stop containers"
	@echo "  make bash         - Enter the app container"
	@echo "  make test         - Run all tests with coverage report"
	@echo "  make test-nicole  - Run NicoleCore tests with coverage"
	@echo "  make test-valerie - Run ValerieStone tests with coverage"
	@echo "  make migrate      - Run migrations"
	@echo "  make mfs          - Rebuild DB (fresh + seed)"
	@echo "  make ci           - Run composer install"
	@echo "  make ni           - Run npm install"
	@echo "  make dev          - Start Vite (React 19)"
	@echo "  make build        - Build frontend for production"
	@echo "  make pacc         - Clear Laravel cache"
	@echo "  make chown        - Fix file permissions"
	@echo "  make cc           - Remove temporary files"
