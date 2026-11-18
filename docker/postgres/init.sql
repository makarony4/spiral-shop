-- Database-per-service: кожен сервіс має власну БД і нічого не знає про чужу.
-- (Для простоти — на одному сервері Postgres.)
CREATE DATABASE catalog;
CREATE DATABASE orders;
