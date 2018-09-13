Сделаны только основные задания, допы сделать не успел.

Использованные библиотеки:
* PHP:
  * doctrine
  * twig
  * symfony/http-foundation
* JS:
  * JQuery
  * [director.js](https://github.com/flatiron/director)
  
Установка и запуск:
* Установить зависимости: 
```bash
$ composer install
```
* Переименовать файл "env.sample.php" в "env.php" и поменять в нем настройки подключения к базе данных
* Создать таблицы в базе данных:
```bash
$ ./vendor/bin/doctrine orm:schema-tool:create
```
* В приложении используется едина точка входа "/public/index.php". 
Поэтому на веб-сервере параметр *root* должен указывать на "/public".
конфиг для nginx можно использовать как для [symfony](https://symfony.com/doc/current/setup/web_server_configuration.html#nginx))
