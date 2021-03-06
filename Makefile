DOCKER_COMPOSE_DIR=./.docker
DOCKER_COMPOSE_FILE=$(DOCKER_COMPOSE_DIR)/docker-compose.yaml
DEFAULT_CONTAINER=workspace
DOCKER_COMPOSE=docker-compose -f $(DOCKER_COMPOSE_FILE) --project-directory $(DOCKER_COMPOSE_DIR)

DEFAULT_GOAL := help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ [Docker] Build / Infrastructure
.docker/.env:
	cp $(DOCKER_COMPOSE_DIR)/.env.dist $(DOCKER_COMPOSE_DIR)/.env
	ssh-keygen -t rsa -b 4096 -f .docker/workspace/.ssh/id_rsa -q -N ""

.PHONY: docker-clean
docker-clean: ## Remove the .env file and SSH keys
	rm -f $(DOCKER_COMPOSE_DIR)/.env
	rm -f $(DOCKER_COMPOSE_DIR)/workspace/.ssh/id_rsa $(DOCKER_COMPOSE_DIR)/workspace/.ssh/id_rsa.pub

.PHONY: docker-init
docker-init: .docker/.env ## Make sure the .env file exists for docker and generate SSH key for workspace

.PHONY: docker-build-from-scratch
docker-build-from-scratch: docker-init ## Build all docker images from scratch, without cache etc. Build a specific image by providing the service name via: make docker-build CONTAINER=<service>
	$(DOCKER_COMPOSE) rm -fs $(CONTAINER) && \
	$(DOCKER_COMPOSE) build --pull --no-cache --parallel $(CONTAINER) && \
	$(DOCKER_COMPOSE) up -d --force-recreate $(CONTAINER)

.PHONY: docker-test
docker-test: docker-init docker-up ## Run the infrastructure tests for the docker setup
	sh $(DOCKER_COMPOSE_DIR)/docker-test.sh

.PHONY: docker-build
docker-build: docker-init ## Build all docker images. Build a specific image by providing the service name via: make docker-build CONTAINER=<service>
	$(DOCKER_COMPOSE) build --parallel $(CONTAINER) && \
	$(DOCKER_COMPOSE) up -d --force-recreate $(CONTAINER)

.PHONY: docker-prune
docker-prune: ## Remove unused docker resources via 'docker system prune -a -f --volumes'
	docker system prune -a -f --volumes

.PHONY: docker-up
docker-up: docker-init ## Start all docker containers. To only start one container, use CONTAINER=<service>
	$(DOCKER_COMPOSE) up -d $(CONTAINER)

.PHONY: docker-down
docker-down: docker-init ## Stop all docker containers. To only stop one container, use CONTAINER=<service>
	$(DOCKER_COMPOSE) down $(CONTAINER)

.PHONY: docker-exec
docker-exec: docker-init ## Run a command in a specific container, use CONTAINER=<service> COMMAND=<command>
	$(DOCKER_COMPOSE) exec $(CONTAINER) $(COMMAND)

.PHONY: install
install: docker-init ## Install the application
	make docker-build
	make docker-up
	make docker-exec CONTAINER=workspace COMMAND="php composer.phar install"
	#FIXME: mysql requires some time to start (5-10 sec). If vendors are already installed, the next commands will fail
	sleep 10
	#FIXME: for some reason mysql doesn't receive connections until a first connection from CLI
	make docker-exec CONTAINER=mysql COMMAND="mysql -h localhost -u real-estate-searcher -preal-estate-searcher -e 'SELECT 1'"
	make docker-exec CONTAINER=workspace COMMAND="bin/console doctrine:migrations:migrate -n"

