SHELL := /bin/bash

export SERVICE_NAME:=slim-json-schema
export NETWORK:=$(SERVICE_NAME)-net

up:
	docker network create environment_slim_json_schema || true
	docker-compose -f ./ops/development/docker/composer.yml -p slim-json-schema-composer up --build
	docker-compose -f ./ops/development/docker/fpm.yml -p slim-json-schema-fpm up -d --build
	docker-compose -f ./ops/development/docker/nginx.yml -p slim-json-schema-nginx up -d --build

down:
	docker rm -f $$(docker ps -aqf "name=slim-json-schema-") || true

prepare:
	docker network create $(NETWORK) || true
	@docker build -t $(SERVICE_NAME)-composer --target composer --build-arg GITHUB_TOKEN=$(GITHUB_TOKEN) -f ops/docker/Dockerfile .

openapi:
	docker run -it --rm \
		-v $(PWD):/opt/src/app \
		-w /opt/src/app \
		slim-json-schema-fpm_slim-json-schema-fpm bin/openapi app:openapi-v3-generator openapi-v3-1-0.json
