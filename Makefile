.PHONY: up down build logs ps test-flow catalog-shell orders-shell proto grpc-call vendor

up:            ## Зібрати та підняти все
	docker compose up -d --build

down:          ## Зупинити і видалити volume з даними
	docker compose down -v

logs:          ## Логи Spiral-сервісів
	docker compose logs -f catalog orders notifications

ps:
	docker compose ps

catalog-shell:
	docker compose exec catalog sh

orders-shell:
	docker compose exec orders sh

test-flow:     ## Наскрізний сценарій: товари -> замовлення -> лист
	./scripts/smoke.sh

# ---------- gRPC ----------
CODEGEN = docker build -q -f docker/php/Dockerfile --target codegen
GRPC_EXT = --build-arg PHP_EXTENSIONS="pdo_pgsql sockets opcache grpc"

proto:         ## Згенерувати PHP-код з proto/ (сервер для catalog, клієнт для orders)
	$(CODEGEN) --build-arg SERVICE=catalog -t spiral-shop-codegen-catalog .
	docker run --rm -v "$(CURDIR)/services/catalog/generated:/app/generated" spiral-shop-codegen-catalog php app.php grpc:generate
	$(CODEGEN) --build-arg SERVICE=orders $(GRPC_EXT) -t spiral-shop-codegen-orders .
	docker run --rm -v "$(CURDIR)/services/orders/generated:/app/generated" spiral-shop-codegen-orders php app.php grpc:generate

grpc-call:     ## Приклад прямого gRPC-виклику catalog: make grpc-call ID=1
	docker run --rm --network spiral-shop_shop -v "$(CURDIR)/proto:/proto" fullstorydev/grpcurl:v1.9.3 \
		-plaintext -import-path /proto -proto catalog/v1/catalog.proto \
		-d '{"id":$(or $(ID),1)}' catalog:9001 catalog.v1.CatalogService/GetProduct

# ---------- локальна розробка ----------
# vendor/ на хості — лише для IDE та лінтерів (в образи він не потрапляє, див. .dockerignore).
# Ставимо тим самим PHP 8.4, що й у контейнерах, тож версії пакетів збігаються з продом.
RUNTIME = docker build -q -f docker/php/Dockerfile --target runtime

vendor:        ## composer install у кожному сервісі (для IDE/лінтерів)
	$(RUNTIME) -t spiral-shop-php-runtime .
	$(RUNTIME) $(GRPC_EXT) -t spiral-shop-php-runtime-grpc .
	for s in catalog notifications; do \
		docker run --rm -v "$(CURDIR)/services/$$s:/app" -w /app spiral-shop-php-runtime composer install --no-interaction --no-progress; \
	done
	docker run --rm -v "$(CURDIR)/services/orders:/app" -w /app spiral-shop-php-runtime-grpc composer install --no-interaction --no-progress
