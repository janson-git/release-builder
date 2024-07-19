.PHONY: help
help:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' ${MAKEFILE_LIST}

.DEFAULT_GOAL := help

up:
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app php artisan migrate
	docker-compose exec app php artisan ide-helper:generate

down:
	docker-compose down

install:
	if [ ! -f ./release-builder-app/.env ] ; then \
		cp ./release-builder-app/.env.example ./release-builder-app/.env \
	; fi
	@echo "\n.env file created"
	docker-compose run app php artisan ide-helper:generate
	docker-compose run app php artisan key:generate
	@echo "Project is ready to start. Type 'make up' to start use."

build:
	docker-compose build
