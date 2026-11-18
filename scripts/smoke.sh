#!/bin/sh
# Наскрізна перевірка: gateway -> orders -(gRPC)-> catalog, orders -> RabbitMQ -> notifications -> Mailpit
set -e
API=${API:-http://localhost:8080}

printf "Чекаємо на gateway"
until curl -sf "$API/api/orders" >/dev/null 2>&1; do printf "."; sleep 2; done; echo

echo "== Список товарів"
curl -s "$API/api/products" ; echo

echo "\n== Створюємо товар"
curl -s -X POST "$API/api/products" -H 'Content-Type: application/json' \
  -d '{"name":"Monitor 27\"","description":"IPS, 144Hz","price":899900,"stock":2}'; echo

echo "\n== Невалідний товар (очікуємо 422)"
curl -s -X POST "$API/api/products" -H 'Content-Type: application/json' -d '{"name":"x","price":0}'; echo

echo "\n== Оформлюємо замовлення"
curl -s -X POST "$API/api/orders" -H 'Content-Type: application/json' \
  -d '{"product_id":1,"quantity":2,"email":"buyer@example.test"}'; echo

echo "\n== Замовлення понад залишок (очікуємо 409)"
curl -s -X POST "$API/api/orders" -H 'Content-Type: application/json' \
  -d '{"product_id":3,"quantity":99,"email":"buyer@example.test"}'; echo

echo "\n== Неіснуючий товар (очікуємо 404)"
curl -s -X POST "$API/api/orders" -H 'Content-Type: application/json' \
  -d '{"product_id":999,"quantity":1,"email":"buyer@example.test"}'; echo

echo "\n== Залишок товару #1 після замовлення"
curl -s "$API/api/products/1"; echo

echo "\n== gRPC напряму: catalog.v1.CatalogService/GetProduct"
docker run --rm --network spiral-shop_shop -v "$(pwd)/proto:/proto" fullstorydev/grpcurl:v1.9.3 \
  -plaintext -import-path /proto -proto catalog/v1/catalog.proto \
  -d '{"id":1}' catalog:9001 catalog.v1.CatalogService/GetProduct

sleep 2
echo "\n== Листи в Mailpit"
curl -s "http://localhost:8025/api/v1/messages" | grep -o '"Subject":"[^"]*"' || true
