init: docker-down \
	app-clear \
	docker-pull docker-build docker-up \
	app-init
up: docker-up
down: docker-down
restart: down up

app-clear:
	docker run --rm -v ${PWD}/app:/app -w /app alpine sh -c 'rm -rf app/var/cache/* var/log/* var/test/*'
	docker run --rm -v ${PWD}/app:/app -w /app alpine sh -c 'rm -rf app/runtime/cache/*'

app-init: app-permissions app-composer-install

app-permissions:
	docker run --rm -v ${PWD}/app:/app -w /app alpine chmod 777 runtime web/assets

app-composer-install:
	docker compose run --rm php composer install

docker-pull:
	docker compose pull

docker-build:
	DOCKER_BUILDKIT=1 COMPOSE_DOCKER_CLI_BUILD=1 docker compose build --build-arg BUILDKIT_INLINE_CACHE=1 --pull

push-dev-cache:
	docker compose push

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

try-build:
	REGISTRY=localhost IMAGE_TAG=0 make build

build: build-frontend build-manticore

build-frontend:
	DOCKER_BUILDKIT=1 docker --log-level=debug build --pull --build-arg BUILDKIT_INLINE_CACHE=1 \
		--target builder \
		--cache-from ${REGISTRY}/common-library-app:cache-builderr \
		--tag ${REGISTRY}/common-library-app:cache-builder \
		--file app/docker/production/Dockerfile app

	DOCKER_BUILDKIT=1 docker --log-level=debug build --pull --build-arg BUILDKIT_INLINE_CACHE=1 \
		--cache-from ${REGISTRY}/common-library-app:cache-builder \
		--cache-from ${REGISTRY}/common-library-app:cache \
		--tag ${REGISTRY}/common-library-app:cache \
		--tag ${REGISTRY}/common-library-app:${IMAGE_TAG} \
        --file app/docker/production/Dockerfile app

build-manticore:
	DOCKER_BUILDKIT=1 docker --log-level=debug build --pull --build-arg BUILDKIT_INLINE_CACHE=1 \
        --cache-from ${REGISTRY}/common-library-manticore:cache \
        --tag ${REGISTRY}/common-library-manticore:cache \
        --tag ${REGISTRY}/common-library-manticore:${IMAGE_TAG} \
        --file docker/Dockerfile app

push-build-cache: push-build-cache-frontend push-build-cache-manticore

push-build-cache-frontend:
	docker push ${REGISTRY}/common-library-app:cache-builder
	docker push ${REGISTRY}/common-library-app:cache

push-build-cache-manticore:
	docker push ${REGISTRY}/common-library-manticore:cache

push:
	docker push ${REGISTRY}/common-library-app:${IMAGE_TAG}
	docker push ${REGISTRY}/common-library-manticore:${IMAGE_TAG}

deploy:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'docker network create --driver=overlay traefik-public || true'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'rm -rf common_${BUILD_NUMBER} && mkdir common_${BUILD_NUMBER}'

	envsubst < docker-compose-production.yml > docker-compose-production-env.yml
	scp -o StrictHostKeyChecking=no -P ${PORT} docker-compose-production-env.yml deploy@${HOST}:common_${BUILD_NUMBER}/docker-compose.yml
	rm -f docker-compose-production-env.yml

	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'mkdir common_${BUILD_NUMBER}/secrets'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cp .secrets_common_library/* common_${BUILD_NUMBER}/secrets'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd common_${BUILD_NUMBER} && docker stack deploy --compose-file docker-compose.yml common-library --with-registry-auth --prune'
