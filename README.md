# Blogabet Telegram Bot
Очень простой скрипт для получения рассылки в Telegram бесплатных ставок на спорт из сервиса https://blogabet.com/ 

## Скрипт уведомляет о 2 типах события:
- 🔴 Live
- 🕜 Линия

## Установка и запуск
- Запустить ```composer install```
- Подключиться к **mysql** базе данных
- Указать доступы к сервису https://blogabet.com/
- Указать ***Telegram token***
- Указать ***Telegram chatID***
- На основе примера вызвать функцию ```getData()``` c необходимыми параметрами


## База данных

Необходимо создать таблицу ```events``` :

| Name   | Type         | Null | Extra          |
|--------|--------------|------|----------------|
| id     | int(11)      | No   | AUTO_INCREMENT |
| uid    | varchar(256) | Yes  | None           |
| source | varchar(256) | No   | None           |
| date   | varchar(256) | No   | None           |


### P.S 
В Telegram необходимо создать группу, в нее пригласить бота. Скрипт можно поставить на **CRON**. Он будет запускаться и присылать все доступные для него ставки блогеров отсеивая те, которые уже прислал ранее