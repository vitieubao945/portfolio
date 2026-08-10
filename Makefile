SHELL := /bin/bash
COMPOSE := docker compose
APP := $(COMPOSE) exec app
NODE := $(COMPOSE) exec node

WP_URL ?= http://localhost:8754
WP_TITLE ?= Portfolio
WP_ADMIN_USER ?= admin
WP_ADMIN_PASSWORD ?= admin_password_123
WP_ADMIN_EMAIL ?= admin@example.test

.DEFAULT_GOAL := help

.PHONY: help up down restart ps logs build \
	install composer-install theme-composer-install yarn-install \
	dev vite-build wp-install theme-activate \
	shell-app shell-node db-shell db-export clean

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-22s\033[0m %s\n", $$1, $$2}'

up: ## Start the dev stack (nginx, php, mariadb, node) in the background
	$(COMPOSE) up -d

down: ## Stop and remove the dev stack (keeps volumes)
	$(COMPOSE) down

restart: ## Restart all services
	$(COMPOSE) restart

ps: ## Show service status
	$(COMPOSE) ps

logs: ## Tail logs for all services (or make logs s=node)
	$(COMPOSE) logs -f $(s)

build: ## (Re)build the app and node images
	$(COMPOSE) build

install: composer-install theme-composer-install yarn-install ## Install PHP + theme + JS dependencies

composer-install: ## composer install (Bedrock root)
	$(APP) composer install

theme-composer-install: ## composer install (Sage theme, e.g. Acorn)
	$(APP) composer install --working-dir=web/app/themes/sage

yarn-install: ## yarn install (Sage theme)
	$(NODE) yarn install

dev: ## Start/restart the Vite dev server with HMR
	$(COMPOSE) up -d node

vite-build: ## Production build of theme assets (public/build)
	$(NODE) yarn build

wp-install: ## First-time WordPress install (wp core install)
	$(APP) wp core install \
		--url="$(WP_URL)" \
		--title="$(WP_TITLE)" \
		--admin_user="$(WP_ADMIN_USER)" \
		--admin_password="$(WP_ADMIN_PASSWORD)" \
		--admin_email="$(WP_ADMIN_EMAIL)" \
		--skip-email

theme-activate: ## Activate the Sage theme
	$(APP) wp theme activate sage

shell-app: ## Open a shell in the app (PHP-FPM) container
	$(APP) sh

shell-node: ## Open a shell in the node container
	$(NODE) sh

db-shell: ## Open a MySQL shell against the database service
	$(APP) wp db cli

db-export: ## Export the database to db-export.sql
	$(APP) wp db export - > db-export.sql

clean: ## Stop the stack and remove volumes (drops the database!)
	$(COMPOSE) down -v
