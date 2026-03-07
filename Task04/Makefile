.PHONY: help install
.DEFAULT_GOAL := help

help:
	@echo "Доступные команды:"
	@echo "  make install  - Установка приложения"

install:
	@echo "Установка зависимостей..."
	@composer install
	@echo "Копирование файла конфигурации..."
	@cp .env.example .env || true
	@echo "Генерация ключа приложения..."
	@php artisan key:generate
	@echo "Создание базы данных..."
	@touch database/database.sqlite
	@echo "Запуск миграций..."
	@php artisan migrate
	@echo "Установка завершена!"
	@echo "Запустите сервер: php artisan serve"