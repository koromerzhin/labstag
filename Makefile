isDocker := $(shell docker info > /dev/null 2>&1 && echo 1)

.DEFAULT_GOAL := help
STACK         := labstag
NETWORK       := proxynetwork

REDIS         := $(STACK)_redis
REDISFULLNAME := $(REDIS).1.$$(docker service ps -f 'name=$(REDIS)' $(REDIS) -q --no-trunc | head -n1)

MAILHOG         := $(STACK)_mailhog
MAILHOGFULLNAME := $(MAILHOG).1.$$(docker service ps -f 'name=$(MAILHOG)' $(MAILHOG) -q --no-trunc | head -n1)

MERCURE         := $(STACK)_mercure
MERCUREFULLNAME := $(MERCURE).1.$$(docker service ps -f 'name=$(MERCURE)' $(MERCURE) -q --no-trunc | head -n1)

MARIADB         := $(STACK)_mariadb
MARIADBFULLNAME := $(MARIADB).1.$$(docker service ps -f 'name=$(MARIADB)' $(MARIADB) -q --no-trunc | head -n1)

APACHE         := $(STACK)_apache
APACHEFULLNAME := $(APACHE).1.$$(docker service ps -f 'name=$(APACHE)' $(APACHE) -q --no-trunc | head -n1)

PHPMYADMIN         := $(STACK)_phpmyadmin
PHPMYADMINFULLNAME := $(PHPMYADMIN).1.$$(docker service ps -f 'name=$(PHPMYADMIN)' $(PHPMYADMIN) -q --no-trunc | head -n1)

PHPFPM         := $(STACK)_phpfpm
PHPFPMFULLNAME := $(PHPFPM).1.$$(docker service ps -f 'name=$(PHPFPM)' $(PHPFPM) -q --no-trunc | head -n1)

DOCKER_EXECPHP := @docker exec $(PHPFPMFULLNAME)

SUPPORTED_COMMANDS := bdd composer contributors docker encore env geocode git inspect install linter logs messenger sleep ssh tests workflow-png update inspect libraries
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  COMMAND_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(COMMAND_ARGS):;@:)
endif

.PHONY: help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

.PHONY: node_modules
node_modules:
	@npm install

dump:
	@mkdir dump

.PHONY: isdocker
isdocker: ## Docker is launch
ifeq ($(isDocker), 0)
	@echo "Docker is not launch"
	exit 1
endif

apps/.env: apps/.env.dist ## Install .env
	@cp apps/.env.dist apps/.env

.PHONY: assets
assets:
	$(DOCKER_EXECPHP) make assets

.PHONY: bdd
bdd: ## Scripts for BDD
ifeq ($(COMMAND_ARGS),fixtures)
	$(DOCKER_EXECPHP) make bdd fixtures
else ifeq ($(COMMAND_ARGS),migrate)
	$(DOCKER_EXECPHP) make bdd migrate
else ifeq ($(COMMAND_ARGS),validate)
	$(DOCKER_EXECPHP) make bdd validate
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make bdd ARGUMENT"
	@echo "---"
	@echo "fixtures: fixtures"
	@echo "migrate: migrate database"
	@echo "validate: bdd validate"
endif

.PHONY: composer
composer: ## Scripts for composer
ifeq ($(COMMAND_ARGS),suggests)
	$(DOCKER_EXECPHP) make composer suggests
else ifeq ($(COMMAND_ARGS),outdated)
	$(DOCKER_EXECPHP) make composer outdated
else ifeq ($(COMMAND_ARGS),fund)
	$(DOCKER_EXECPHP) make composer fund
else ifeq ($(COMMAND_ARGS),prod)
	$(DOCKER_EXECPHP) make composer prod
else ifeq ($(COMMAND_ARGS),dev)
	$(DOCKER_EXECPHP) make composer dev
else ifeq ($(COMMAND_ARGS),u)
	$(DOCKER_EXECPHP) make composer update
else ifeq ($(COMMAND_ARGS),i)
	$(DOCKER_EXECPHP) make composer install
else ifeq ($(COMMAND_ARGS),validate)
	$(DOCKER_EXECPHP) make composer validate
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make composer ARGUMENT"
	@echo "---"
	@echo "suggests: suggestions package pour PHP"
	@echo "i: install"
	@echo "outdated: Packet php outdated"
	@echo "fund: Discover how to help fund the maintenance of your dependencies."
	@echo "prod: Installation version de prod"
	@echo "dev: Installation version de dev"
	@echo "u: COMPOSER update"
	@echo "validate: COMPOSER validate"
endif

.PHONY: contributors
contributors: ## Contributors
ifeq ($(COMMAND_ARGS),add)
	@npm run contributors add
else ifeq ($(COMMAND_ARGS),check)
	@npm run contributors check
else ifeq ($(COMMAND_ARGS),generate)
	@npm run contributors generate
else
	@npm run contributors
endif

.PHONY: docker
docker: ## Scripts docker
ifeq ($(COMMAND_ARGS),create-network)
	@docker network create --driver=overlay $(NETWORK)
else ifeq ($(COMMAND_ARGS),image-pull)
	@more docker-compose.yml | grep image: | sed -e "s/^.*image:[[:space:]]//" | while read i; do docker pull $$i; done
else ifeq ($(COMMAND_ARGS),deploy)
	@docker stack deploy -c docker-compose.yml $(STACK)
else ifeq ($(COMMAND_ARGS),ls)
	@docker stack services $(STACK)
else ifeq ($(COMMAND_ARGS),stop)
	@docker stack rm $(STACK)
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make docker ARGUMENT"
	@echo "---"
	@echo "create-network: create network"
	@echo "deploy: deploy"
	@echo "ls: docker service"
	@echo "stop: docker stop"
endif

.PHONY: encore
encore: ## Script for Encore
ifeq ($(COMMAND_ARGS),dev)
	@npm rebuild node-sass
	@npm run encore-dev
else ifeq ($(COMMAND_ARGS),watch)
	@npm run encore-watch
else ifeq ($(COMMAND_ARGS),build)
	@npm run encore-build
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make encore ARGUMENT"
	@echo "---"
	@echo "dev: créer les assets en version dev"
	@echo "watch: créer les assets en version watch"
	@echo "build: créer les assets en version prod"
endif

.PHONY: folders
folders: dump ## Create folder

.PHONY: env
env: apps/.env ## Scripts Installation environnement
ifeq ($(COMMAND_ARGS),dev)
	@sed -i 's/APP_ENV=prod/APP_ENV=dev/g' apps/.env
else ifeq ($(COMMAND_ARGS),prod)
	@sed -i 's/APP_ENV=dev/APP_ENV=prod/g' apps/.env
	@rm -rf apps/vendor
	@make composer prod -i
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make env ARGUMENT"
	@echo "---"
	@echo "dev: environnement dev"
	@echo "prod: environnement prod"
endif

.PHONY: geocode
geocode: ## Geocode
	$(DOCKER_EXECPHP) make geocode $(COMMAND_ARGS)

.PHONY: git
git: ## Scripts GIT
ifeq ($(COMMAND_ARGS),u)
	@git pull
else ifeq ($(COMMAND_ARGS),status)
	@git status
else ifeq ($(COMMAND_ARGS),check)
	@make composer validate -i
	@make composer outdated -i
	@make bdd validate -i
	@make contributors check -i
	@make linter all -i
	@make git status -i
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make git ARGUMENT"
	@echo "---"
	@echo "u: Update git"
	@echo "check: CHECK before"
	@echo "status: status"
endif

.PHONY: inspect
inspect: ## docker service inspect
ifeq ($(COMMAND_ARGS),redis)
	@docker service inspect $(REDIS)
else ifeq ($(COMMAND_ARGS),mailhog)
	@docker service inspect $(MAILHOG)
else ifeq ($(COMMAND_ARGS),mercure)
	@docker service inspect $(MERCURE)
else ifeq ($(COMMAND_ARGS),mariadb)
	@docker service inspect $(MARIADB)
else ifeq ($(COMMAND_ARGS),apache)
	@docker service inspect $(APACHE)
else ifeq ($(COMMAND_ARGS),phpmyadmin)
	@docker service inspect $(PHPMYADMIN)
else ifeq ($(COMMAND_ARGS),phpfpm)
	@docker service inspect $(PHPFPM)
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make inspect ARGUMENT"
	@echo "---"
	@echo "stack: inspect stack"
	@echo "redis: REDIS"
	@echo "mailhog: MAILHOG"
	@echo "mercure: MERCURE"
	@echo "mariadb: MARIADB"
	@echo "apache: APACHE"
	@echo "phpmyadmin: PHPMYADMIN"
	@echo "phpfpm: PHPFPM"
endif

.PHONY: install
install: folders apps/.env ## installation
ifeq ($(COMMAND_ARGS),all)
	@make node_modules -i
	@make docker image-pull -i
	@make docker deploy -i
	@make sleep 60 -i
	@make bdd migrate -i
	@make assets -i
	@make encore dev -i
	@make linter all -i
else ifeq ($(COMMAND_ARGS),dev)
	@make install all -i
	@make bdd fixtures -i
	@make commands -i
	@make env dev -i
else ifeq ($(COMMAND_ARGS),prod)
	@make install all -i
	@make bdd fixtures -i
	@make commands -i
	@make env prod -i
	@make encore build -i
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make install ARGUMENT"
	@echo "---"
	@echo "all: common"
	@echo "dev: dev"
	@echo "prod: prod"
endif

.PHONY: commands
commands:
	$(DOCKER_EXECPHP) symfony console labstag:install --all
	$(DOCKER_EXECPHP) symfony console labstag:guard-route
	$(DOCKER_EXECPHP) symfony console labstag:workflows-show

.PHONY: linter
linter: ## Scripts Linter
ifeq ($(COMMAND_ARGS),all)
	@make linter phpfix -i
	@make linter eslint -i
	@make linter stylelint-fix -i
	@make linter twig -i
	@make linter container -i
	@make linter yaml -i
	@make linter phpstan -i
	@make linter phpcs -i
	@make linter phpmd -i
	@make linter readme -i
else ifeq ($(COMMAND_ARGS),phpfix)
	@make linter php-cs-fixer -i
	@make linter phpcbf -i
else ifeq ($(COMMAND_ARGS),readme)
	@npm run linter-markdown README.md
else ifeq ($(COMMAND_ARGS),stylelint)
	@npm run stylelint
else ifeq ($(COMMAND_ARGS),stylelint-fix)
	@npm run stylelint-fix
else ifeq ($(COMMAND_ARGS),eslint)
	@npm run eslint
else ifeq ($(COMMAND_ARGS),eslint-fix)
	@npm run eslint-fix
else ifeq ($(COMMAND_ARGS),php-cs-fixer)
	$(DOCKER_EXECPHP) make linter php-cs-fixer
else ifeq ($(COMMAND_ARGS),phpcbf)
	$(DOCKER_EXECPHP) make linter phpcbf
else ifeq ($(COMMAND_ARGS),phpcs)
	$(DOCKER_EXECPHP) make linter phpcs
else ifeq ($(COMMAND_ARGS),phpcs-onlywarning)
	$(DOCKER_EXECPHP) make linter phpcs-onlywarning
else ifeq ($(COMMAND_ARGS),phpcs-onlyerror)
	$(DOCKER_EXECPHP) make linter phpcs-onlyerror
else ifeq ($(COMMAND_ARGS),phploc)
	$(DOCKER_EXECPHP) make linter phploc
else ifeq ($(COMMAND_ARGS),phpmd)
	$(DOCKER_EXECPHP) make linter phpmd
else ifeq ($(COMMAND_ARGS),phpmnd)
	$(DOCKER_EXECPHP) make linter phpmnd
else ifeq ($(COMMAND_ARGS),phpstan)
	$(DOCKER_EXECPHP) make linter phpstan
else ifeq ($(COMMAND_ARGS),twig)
	$(DOCKER_EXECPHP) make linter twig
else ifeq ($(COMMAND_ARGS),container)
	$(DOCKER_EXECPHP) make linter container
else ifeq ($(COMMAND_ARGS),yaml)
	$(DOCKER_EXECPHP) make linter yaml
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make linter ARGUMENT"
	@echo "---"
	@echo "all: ## Launch all linter"
	@echo "readme: linter README.md"
	@echo "phpfix: PHP-CS-FIXER & PHPCBF"
	@echo "stylelint: indique les erreurs dans le code SCSS"
	@echo "stylelint-fix: fix les erreurs dans le code SCSS"
	@echo "eslint: indique les erreurs sur le code JavaScript à partir d'un standard"
	@echo "eslint-fix: fixe le code JavaScript à partir d'un standard"
	@echo "phpcbf: fixe le code PHP à partir d'un standard"
	@echo "php-cs-fixer: fixe le code PHP à partir d'un standard"
	@echo "phpcs: indique les erreurs de code non corrigé par PHPCBF"
	@echo "phpcs-onlywarning: indique les erreurs de code non corrigé par PHPCBF"
	@echo "phpcs-onlyerror: indique les erreurs de code non corrigé par PHPCBF"
	@echo "phploc: phploc"
	@echo "phpmd: indique quand le code PHP contient des erreurs de syntaxes ou des erreurs"
	@echo "phpmnd: Si des chiffres sont utilisé dans le code PHP, il est conseillé d'utiliser des constantes"
	@echo "phpstan: regarde si le code PHP ne peux pas être optimisé"
	@echo "twig: indique les erreurs de code de twig"
	@echo "container: indique les erreurs de code de container"
	@echo "yaml: indique les erreurs de code de yaml"
endif

.PHONY: logs
logs: ## Scripts logs
ifeq ($(COMMAND_ARGS),stack)
	@docker service logs -f --tail 100 --raw $(STACK)
else ifeq ($(COMMAND_ARGS),redis)
	@docker service logs -f --tail 100 --raw $(REDISFULLNAME)
else ifeq ($(COMMAND_ARGS),mailhog)
	@docker service logs -f --tail 100 --raw $(MAILHOGFULLNAME)
else ifeq ($(COMMAND_ARGS),mercure)
	@docker service logs -f --tail 100 --raw $(MERCUREFULLNAME)
else ifeq ($(COMMAND_ARGS),mariadb)
	@docker service logs -f --tail 100 --raw $(MARIADBFULLNAME)
else ifeq ($(COMMAND_ARGS),apache)
	@docker service logs -f --tail 100 --raw $(APACHEFULLNAME)
else ifeq ($(COMMAND_ARGS),phpmyadmin)
	@docker service logs -f --tail 100 --raw $(PHPMYADMINFULLNAME)
else ifeq ($(COMMAND_ARGS),phpfpm)
	@docker service logs -f --tail 100 --raw $(PHPFPMFULLNAME)
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make logs ARGUMENT"
	@echo "---"
	@echo "stack: logs stack"
	@echo "redis: REDIS"
	@echo "mailhog: MAILHOG"
	@echo "mercure: MERCURE"
	@echo "mariadb: MARIADB"
	@echo "apache: APACHE"
	@echo "phpmyadmin: PHPMYADMIN"
	@echo "phpfpm: PHPFPM"
endif

.PHONY: messenger
messenger: ## Scripts messenger
ifeq ($(COMMAND_ARGS),consule)
	$(DOCKER_EXECPHP) make messenger consume
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make messenger ARGUMENT"
	@echo "---"
	@echo "consume: Messenger Consume"
endif

.PHONY: update
update: ## docker service update
ifeq ($(COMMAND_ARGS),redis)
	@docker service update $(REDIS)
else ifeq ($(COMMAND_ARGS),mailhog)
	@docker service update $(MAILHOG)
else ifeq ($(COMMAND_ARGS),mercure)
	@docker service update $(MERCURE)
else ifeq ($(COMMAND_ARGS),mariadb)
	@docker service update $(MARIADB)
else ifeq ($(COMMAND_ARGS),apache)
	@docker service update $(APACHE)
else ifeq ($(COMMAND_ARGS),phpmyadmin)
	@docker service update $(PHPMYADMIN)
else ifeq ($(COMMAND_ARGS),phpfpm)
	@docker service update $(PHPFPM)
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make service-update ARGUMENT"
	@echo "---"
	@echo "stack: logs stack"
	@echo "redis: REDIS"
	@echo "mailhog: MAILHOG"
	@echo "mercure: MERCURE"
	@echo "mariadb: MARIADB"
	@echo "apache: APACHE"
	@echo "phpmyadmin: PHPMYADMIN"
	@echo "phpfpm: PHPFPM"
endif

.PHONY: sleep
sleep: ## sleep
	@sleep  $(COMMAND_ARGS)

.PHONY: ssh
ssh: ## SSH
ifeq ($(COMMAND_ARGS),redis)
	@docker exec -it $(REDISFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),mailhog)
	@docker exec -it $(MAILHOGFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),mercure)
	@docker exec -it $(MERCUREFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),mariadb)
	@docker exec -it $(MARIADBFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),apache)
	@docker exec -it $(APACHEFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),phpmyadmin)
	@docker exec -it $(PHPMYADMINFULLNAME) /bin/bash
else ifeq ($(COMMAND_ARGS),phpfpm)
	@docker exec -it $(PHPFPMFULLNAME) /bin/bash
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make ssh ARGUMENT"
	@echo "---"
	@echo "redis: REDIS"
	@echo "mailhog: MAILHOG"
	@echo "mercure: MERCURE"
	@echo "mariadb: MARIADB"
	@echo "apache: APACHE"
	@echo "phpmyadmin: PHPMYADMIN"
	@echo "phpfpm: PHPFPM"
endif

.PHONY: tests
tests: ## Scripts tests
ifeq ($(COMMAND_ARGS),launch)
	@docker exec $(PHPFPMFULLNAME) make tests all
else ifeq ($(COMMAND_ARGS),behat)
	@docker exec $(PHPFPMFULLNAME) make tests behat
else ifeq ($(COMMAND_ARGS),simple-phpunit-unit-integration)
	@docker exec $(PHPFPMFULLNAME) make tests simple-phpunit-unit-integration
else ifeq ($(COMMAND_ARGS),simple-phpunit)
	@docker exec $(PHPFPMFULLNAME) make tests simple-phpunit
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make tests ARGUMENT"
	@echo "---"
	@echo "launch: Launch all tests"
	@echo "behat: Lance les tests behat"
	@echo "simple-phpunit-unit-integration: lance les tests phpunit"
	@echo "simple-phpunit: lance les tests phpunit"
endif

.PHONY: translations
translations: ## update translation
	$(DOCKER_EXECPHP) make translations

.PHONY: workflow-png
workflow-png: ## generate workflow png
	$(DOCKER_EXECPHP) make workflow-png $(COMMAND_ARGS)

.PHONY: libraries
libraries: ## Add libraries
ifeq ($(COMMAND_ARGS),tarteaucitron)
	wget https://github.com/AmauriC/tarteaucitron.js/archive/refs/tags/v1.9.3.zip
	unzip v1.9.3.zip
	rm v1.9.3.zip
	mv tarteaucitron.js-1.9.3 apps/public/tarteaucitron
else
	@echo "ARGUMENT missing"
	@echo "---"
	@echo "make libraries ARGUMENT"
	@echo "---"
	@echo "tarteaucitron: tarteaucitron"
endif

.PHONY: upgrade
upgrade: ## upgrade git
	$(DOCKER_EXECPHP) symfony console labstag:update --maintenanceon
	@make git u -i
	@make composer i -i
	@make bdd migrate -i
	@make node_modules -i
	@make encore build -i
	@make commands -i
	$(DOCKER_EXECPHP) symfony console cache:clear
	$(DOCKER_EXECPHP) symfony console labstag:update --maintenanceoff
