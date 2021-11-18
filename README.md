### Hexlet tests and linter status:
[![Actions Status](https://github.com/kemallt/php-project-lvl3/workflows/hexlet-check/badge.svg)](https://github.com/kemallt/php-project-lvl3/actions)
### Linttest status
[![linttest](https://github.com/kemallt/php-project-lvl3/actions/workflows/linttest.yml/badge.svg)](https://github.com/kemallt/php-project-lvl3/actions/workflows/linttest.yml)
### CodeClimate maintainability
[![Maintainability](https://api.codeclimate.com/v1/badges/1e37ea63cb8cca0db1df/maintainability)](https://codeclimate.com/github/kemallt/php-project-lvl3/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/1e37ea63cb8cca0db1df/test_coverage)](https://codeclimate.com/github/kemallt/php-project-lvl3/test_coverage)

# Demo
https://kemallt3.herokuapp.com/

## Простое приложение для проверки доступности и SEO пригодности страниц

## Requirements
* PHP ^8.0
* Composer
* SQLite or PostgresQL

### Setup
```shell
$ make setup
```
### Run
```shell
$ make start
```
### Test
```shell
$ make test
```
### Lint
```shell
$ make lint
```
### Deploy
```shell
$ make deploy
```
### Log
```shell
$ make log
```

## Using
Введите URL в форме на главной странице. В случае отстуствия данного URL в базе, он будет добавлен.
На открывшейся странице с подробностями по URL можно увидеть дату добавления его в базу и список проверок с результатами.
По нажатию "Запустить проверку" на URL будет отправлен запрос, код ответа сервера на запрос, значения полей title и description открывшейся страницы будут добавлены в таблицу проверок вместе с датой проверки
