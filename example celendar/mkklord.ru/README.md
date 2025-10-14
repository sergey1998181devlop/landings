Для сборки локального окружения разработчика в Docker необходимо.

1. Склонировать к себе проект на рабочее устройство

`git clone git@gcib0d3c8bstra.boostra.ru:boostra/boostra.git`

2. Переключиться на ветку dev

`git checkout dev`

3. Скопировать к себе в папку с проектом config.php по пути  ./config/config.php . 

**За config.php можно обратиться к ребятам**

4. В config.php заменить address до бд с **127.0.0.1** на **51.250.111.252**

5. Запустить Docker-Compose файл из корня проекта.

`docker-compose up -d`

6.Если необходимо пресобрать контейнер выполняем следующую команду:

`docker-compose up -d --build`

**ВАЖНОЕ ЗАМЕЧАНИЕ ЧТО БЫ ВСЕ КАРТИНКИ ПОЯВИЛИСЬ НЕОБХОДИМО СКАЧАТЬ ПАПКУ design c продуктового сайта**

## Сайт будет доступен 127.0.0.1:8088


### Процесс разработки

### Главный принцип сохранение актуальности кода в dev и master_conf.

[ ] С большой силой приходит большая ответственность [ ]

### Фичевые ветки создаются от master_conf ветки

`git checkout master`

`git checkout -b <feat-1>`

### Когда работа с веткой feat-1 закончена, необходимо её запушить в репозиторий.

`git add <changed files>`

`git commit -m "Что делает эта фича"`

`git push origin feat-1`

### Когда ветка feat-1 запушена в репозиторий её необходимо серджить с dev.

**Это можно сделать через GUI Gitlab либо через команды git**

- для примера

`git checkcout dev`

`git merge feat-1`

`git push origin dev`

### После того как ветка попала в dev, её можно отдавать на тестирование.

**Если есть апрув от аналитика/тестировщик повторяем процедуру merge для master ветки**

`git checkcout master_conf`

`git merge feat-1`

`git push origin master_conf`

### После того как код в ветке master_conf, работу над фичей можно закончить.

**Что делать если моя ветка уже в dev и требует доработки**

- Без паники!!!

`git checkout feat-1`

`git commit -m "fix feat-1"`

`git push origin feat-1`

- по новой мерджим с dev и даем отмашку об исправлении.

**Что делать если моя ветка уже в master_conf и требует доработки**

`git checkout master_conf`

`git checkout -b fix-feat-1`

`git commit -m "fix feat-1"`

`git push origin fix-feat-1`

### FIX ветки проходят полный жизенный цикл мердж в dev и master_conf

### Не совершает ошибок, только тот кто не работает разработчиком. 
### Всем успехов в работе!
