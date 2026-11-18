# Spiral Shop — мікросервіси на Spiral Framework + RoadRunner

Навчальний проєкт: як виглядає типовий застосунок на [Spiral](https://spiral.dev) для тих, хто приходить з Laravel/Symfony.
Домен навмисно банальний — міні-магазин: каталог товарів, замовлення, email-сповіщення.

## Архітектура

```
                    ┌──────────────┐
  HTTP :8080 ──────▶│   gateway    │  nginx (API Gateway)
                    └──┬────────┬──┘
          /api/products│        │/api/orders
                       ▼        ▼
              ┌──────────┐  gRPC :9001  ┌──────────┐
              │ catalog  │◀─────────────│  orders  │
              │ Spiral+RR│ ReserveStock │ Spiral+RR│
              │ http+grpc│              │  + ext-grpc
              └────┬─────┘              └──┬────┬──┘
                   │                       │    │ push "order.created"
                   ▼                       ▼    ▼   (async)
              [pg: catalog]         [pg: orders]  RabbitMQ
                                                    │
                                                    ▼
                                          ┌────────────────┐
                                          │ notifications  │ RR Jobs consumer
                                          │  Spiral + RR   │──▶ Mailpit (SMTP)
                                          └────────────────┘

           proto/catalog/v1/catalog.proto — єдиний контракт для catalog і orders
```

| Сервіс          | Що робить                                                        | Spiral-компоненти                                     |
|-----------------|------------------------------------------------------------------|-------------------------------------------------------|
| `catalog`       | CRUD товарів (HTTP), gRPC-сервер `CatalogService` для інших сервісів | HTTP + **gRPC** у одному RoadRunner, Router (атрибути), Filters+Validator, Cycle ORM, Migrations, Console |
| `orders`        | Оформлення замовлення: gRPC-виклик у catalog, подія в RabbitMQ   | Те саме + **згенерований gRPC-клієнт**, Queue (RoadRunner Jobs / AMQP) |
| `notifications` | Слухає чергу, надсилає лист                                      | Лише Queue consumer — без HTTP і БД                  |
| `gateway`       | nginx — єдина точка входу                                       | —                                                     |

Принципи: **database per service**, жодного спільного коду між сервісами (контракт — це JSON і ім'я задачі `order.created`), синхронна взаємодія по gRPC там, де потрібна відповідь (резерв товару), асинхронна — там, де ні (листи).

## Запуск

```bash
make up          # або: docker compose up -d --build
make test-flow   # наскрізний сценарій через curl
make logs
```

| URL                         | Що там                                   |
|-----------------------------|------------------------------------------|
| http://localhost:8080       | API Gateway                              |
| http://localhost:8025       | Mailpit — листи, які надіслав notifications |
| http://localhost:15672      | RabbitMQ UI (guest / guest)              |

### API

```bash
# Товари
curl localhost:8080/api/products
curl localhost:8080/api/products/1
curl -X POST localhost:8080/api/products -H 'Content-Type: application/json' \
     -d '{"name":"Monitor","description":"27\"","price":899900,"stock":3}'

# Замовлення
curl -X POST localhost:8080/api/orders -H 'Content-Type: application/json' \
     -d '{"product_id":1,"quantity":2,"email":"me@example.test"}'
curl localhost:8080/api/orders/1
curl 'localhost:8080/api/orders?email=me@example.test'
```

Помилки: `422` — валідація, `404` — немає товару/замовлення, `409` — недостатньо залишку, `503` — catalog недоступний.

## Spiral очима Laravel/Symfony-розробника

| Laravel / Symfony                         | Spiral                                                                 | Де подивитись |
|-------------------------------------------|------------------------------------------------------------------------|---------------|
| php-fpm + nginx, стан вмирає після запиту | **RoadRunner**: воркери живуть довго, ядро бутиться один раз           | `.rr.yaml`, `app.php` |
| `bootstrap/app.php` / `Kernel::registerBundles()` | `Kernel` зі списком **bootloader-ів**                         | `app/src/Application/Kernel.php` |
| ServiceProvider / `services.yaml`         | **Bootloader** (`SINGLETONS`, `BINDINGS`, `init()`, `boot()`)          | `Bootloader/AppBootloader.php` |
| `routes/api.php` / `#[Route]` Symfony     | `#[Route]` з `spiral/router` + `RoutesBootloader` для middleware       | `Endpoint/Web/*Controller.php` |
| FormRequest / Symfony Form + Validator    | **Filter** (`#[Post]`, `#[Query]`…) + `FilterDefinition` з правилами   | `Endpoint/Web/Filter/` |
| Eloquent (ActiveRecord)                   | **Cycle ORM** (DataMapper, як Doctrine), атрибути на сутностях        | `Domain/*/Product.php`, `Order.php` |
| `php artisan migrate`                     | `php app.php migrate` (`cycle:migrate` — згенерувати з сутностей)      | `app/database/Migrations/` |
| `config/*.php` + `config('x.y')`          | `app/config/*.php` (+ типізований `InjectableConfig`)                 | `app/config/grpc.php`, `queue.php` |
| Artisan command / Symfony Command         | `#[AsCommand]` + `__invoke()` з DI-аргументами                        | `Endpoint/Console/SeedProductsCommand.php` |
| Laravel Queue / Symfony Messenger         | **spiral/queue** поверх **RoadRunner Jobs** (AMQP, Kafka, SQS…)        | `config/queue.php`, `Endpoint/Job/` |
| Middleware                                 | Чистий PSR-15                                                         | `Endpoint/Web/Middleware/` |
| Laravel Facades / Symfony autowiring       | Autowiring через конструктор, без фасадів                              | скрізь |

### Важливо про long-running воркери

У Laravel кожен запит — «чистий аркуш». У Spiral воркер обробляє тисячі запитів поспіль, тому:
- не зберігайте стан запиту у властивостях singleton-сервісів (тому сервіси тут `readonly`);
- Cycle ORM-bridge сам чистить Identity Map (heap) між запитами — через `FinalizerInterface`;
- після зміни коду воркери треба перезапустити (`rr reset` або рестарт контейнера).

### Підводні камені, на які легко натрапити

- **Параметри маршруту — це рядки.** Spiral суворо перевіряє типи аргументів екшену, тож `show(int $id)` для `/products/<id:\d+>` дасть `400 Bad Request`. Пишіть `show(string $id)` і приводьте тип самі.
- **Ретраї в черзі тільки для `RetryableExceptionInterface`.** `#[RetryPolicy]` на обробнику сам по собі нічого не ретраїть: тимчасові збої треба загорнути у свій виняток (див. `notifications/app/src/Domain/TemporaryDeliveryFailure.php`). Для атрибутів потрібен `AttributesBootloader`.
- **Логи.** Bootloader RoadRunner вішає хендлер на канал `roadrunner`, тому потрібен `MONOLOG_DEFAULT_CHANNEL=roadrunner`, інакше `LoggerInterface` пише в нікуди.
- **Імена файлів міграцій** мають формат `20261117.000000_0_create_products_table.php` (`Ymd.His_chunk_name`).
- **Кілька воркерів стартують одночасно** і можуть паралельно створювати кеш у `runtime/`. Тому в Dockerfile кеш прогрівається заздалегідь (`RUN php app.php`).
- **Повідомлення між сервісами — тільки JSON** (`defaultSerializer: json`). Якщо пушити PHP-об'єкт, у заголовок піде ім'я класу, якого в сервісі-споживачі немає.

## gRPC між orders і catalog

```
proto/catalog/v1/catalog.proto        ← контракт (єдине джерело правди)
        │  make proto  (php app.php grpc:generate: protoc + protoc-gen-php-grpc)
        ├─▶ services/catalog/generated/   повідомлення + CatalogServiceInterface
        └─▶ services/orders/generated/    те саме + CatalogServiceClient, GRPCServicesConfig, ServiceBootloader
```

**Сервер (catalog).** RoadRunner слухає `:9001` (секція `grpc` у `.rr.yaml`) і передає виклики тим самим PHP-воркерам. Ви лише реалізуєте згенерований інтерфейс (`app/src/Endpoint/GRPC/CatalogService.php`), а Spiral знаходить клас сам. Доменні винятки перетворює на gRPC-статуси `DomainExceptionInterceptor`: `NOT_FOUND`, `FAILED_PRECONDITION`.

**Клієнт (orders).** Згенерований `ServiceBootloader` прив'язує `CatalogServiceInterface` до `CatalogServiceClient` (адреса береться з `CATALOG_SERVICE_HOST`). Домен orders про gRPC не знає: він залежить від `CatalogClientInterface`, а `GrpcCatalogClient` — лише адаптер, який виставляє дедлайн і переводить gRPC-статуси в доменні винятки.

Що варто знати:
- **Клієнту потрібне `ext-grpc`** (сервер на RoadRunner його не потребує). Перша збірка образу orders компілює розширення довго (~10–20 хв), далі шар береться з кешу.
- **Згенерований клієнт не кидає винятків**: він повертає `[$response, $status]` і ігнорує статус. Тому в orders є `ThrowOnErrorStatusInterceptor` (`app/config/grpcServices.php`).
- **Завжди виставляйте дедлайн** (`options.timeout`): без нього завислий catalog блокує HTTP-воркер orders.
- Код у `generated/` — у репозиторії. Після зміни `.proto` запустіть `make proto`. Якщо прибрали повідомлення з контракту, видаліть застарілі файли вручну.

```bash
make proto          # перегенерувати код
make grpc-call ID=1 # викликати catalog напряму через grpcurl
```

## Структура сервісу

```
services/orders/
├── app.php                      # єдина точка входу (HTTP-воркер, jobs-воркер, CLI)
├── .rr.yaml                     # конфіг RoadRunner: http, grpc, jobs, amqp, rpc
├── docker-entrypoint.sh         # міграції перед стартом
├── composer.json
├── generated/                   # код, згенерований з proto/ (make proto)
└── app/
    ├── config/                  # database.php, queue.php, grpc.php ...
    ├── database/Migrations/
    └── src/
        ├── Application/         # Kernel + Bootloader-и (інфраструктурна "обв'язка")
        ├── Domain/              # сутності, репозиторії, сервіси, доменні винятки
        ├── Infrastructure/      # реалізації інтерфейсів (gRPC-адаптер до catalog)
        └── Endpoint/            # вхідні точки: Web (контролери, фільтри), GRPC, Console, Job
```

Це та сама структура, що й в офіційному скелеті `spiral/app` (`Application` / `Domain` / `Endpoint`).

## Корисні команди

```bash
docker compose exec catalog php app.php                 # список команд
docker compose exec catalog php app.php route:list      # маршрути
docker compose exec catalog php app.php migrate:status
docker compose exec catalog php app.php cycle:migrate   # згенерувати міграцію з сутностей
docker compose exec orders  rr workers                  # стан воркерів RoadRunner
docker compose exec orders  rr jobs list                # пайплайни черг
```

## Що можна додати далі

- mTLS для gRPC, retry/circuit breaker на клієнті.
- **Transactional Outbox** у orders — зараз між записом у БД і публікацією в чергу немає атомарності.
- Тести (`spiral/testing`), Temporal для саг, OpenTelemetry-трейсинг між сервісами.
