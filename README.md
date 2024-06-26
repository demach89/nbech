# Описание
Подготовка файла-выгрузки по стандарту передачи данных НБКИ RUTDF 6.02<br>

# Цель
Подготовка данных к передаче:
1. по микрокредитам (C18_TRADE[4]);
2. приобретённых в результаты цессии (C18_TRADE[18]);
3. для регулярной отправки по событиям 2.3 (для открытых), 2.5 (для закрытых)<br>
   операционного кода "B" (кредитная информация изменяется или дополняется);
4. по событию 3.3 операционного кода "C.2" — для удаления данных

# Требования
* PHP ^8.0
* php_sqlsrv (для работы с MSSQL)

## Интерфейс
- рабочая страница: index.php

## Поставщики данных
- Google
- MSSQL
- CSV

## Шаблоны поставщиков данных
- templates/google_template.xslx
- templates/csv_template.csv

## Конфигурация
Core/env.example.php -> Core/env.php

## Принцип работы
Уровень пользователя:
1) определение поставщика данных;
2) определение даты начала периода*1;
3) определение цели;<br>

Уровень системы:
1) подготовка данных;
2) определение типа запроса;
3) инициирование запроса поставщику;
4) подготовка данных поставщиком;
5) получение данных от поставщика*2;
6) подготовка выгрузки;
7) подготовка справочного файла комментариев по выгрузке<br>
   *1 нижняя граница выборки; договоры, последние изменения по которым были ранее этой даты - игнорируются<br>
   *2 договоры с пустым полем "UID" игнорируются

# Поставщик данных Google
Инициализация поставщика: GOOGLE_WEB_APPS (конфигурация). <br>
Получает данные с Google-таблиц посредством основанных на них web-app (GAS). <br>
Ссылки на веб-приложения таблиц должны быть внесены в конфигурацию (NBKI_SERVICE_GOOGLE).<br>
Набор переданных данных должен соответствовать шаблону.<br>
[Пример таблицы-приложения](https://docs.google.com/spreadsheets/d/13gXwICP1Qf-baW3qt5XaJ6AbBI3SGAbOZ_AIUmEu-TA/edit)

**Типы запросов**
- общий — работает с вкладками портфелей таблицы (google_all_in_1.php);
- таргетный — работает с вкладкой "TARGET" таблицы (google.php);
- удаление — работает с вкладкой "TARGET" таблицы (google_deleteOrder.php)

**Для таргетного запроса по договору**
1) скопировать значения строки по нужному договору во вкладку "TARGET" таблицы*;
2) сделать таргетный запрос<br>
   (*) - решение обусловлено возможностью внести ручные правки до подготовки выгрузки

**Для запроса на удаление договора из НБКИ**
1) скопировать значения строки по нужному договору во вкладку "TARGET" таблицы;
2) скопировать значения строки по нужному договору во вкладку "DELETE" таблицы (для истории, восстановления);
3) очистить поле "UID" договора во вкладке портфеля таблицы НБКИ;
4) инициировать удаляющий запрос

**Добавление портфеля в сервис GAS**
1. Определить лист таблицы под новый портфель;
2. Внести наименование листа в конфигурацию GAS;
3. Добавить новый портфель в существующий сервис, обслуживающий портфель (NBKI_SERVICE_GOOGLE)

**Добавление сервиса GAS**
1. Аналогично подготовить таблицу, развернуть web-app;
2. Добавить новый сервис в набор GOOGLE_WEB_APPS;
3. Добавить портфель в новый сервис (см Добавление портфеля в сервис GAS)
4. Определить ассоциации набора данных (Core\Assist\TableDataAssoc)
5. Определить класс определения сервиса, обслуживающего портфель (Core\Portfolio\PortfolioServiceDefiner)

# Поставщик данных MSSQL
Получает данные из СУБД MSSQL. <br>

**Типы запросов**
- общий — полная выборка займов к отправке с учётом даты начала периода (mssql_all_in_1.php);
- таргетный — работает с указанием номера договора (mssql_target.php);
- удаление — работает с вкладкой "TARGET" таблицы (mssql_delete.php)

**Для таргетного запроса по договору**
1) вписать номер договора;
2) сделать таргетный запрос<br>
   (*) возвращает договор в дальнейшие выборки, если ранее был удалён

**Для запроса на удаление договора из НБКИ**
1) вписать номер договора;
2) сделать удаляющий запрос*<br>
   (*) исключает договор из дальнейшей выборки
 
# Поставщик данных CSV
Получает данные из CSV, игнорируя заголовки. <br>
Набор переданных данных должен соответствовать шаблону

**Типы запросов**
- общий (CSV_all_in_1.php);
- удаление (CSV_delete.php)

## Результат
- файл выгрузки с именем типа "DTXXXXXXXXX_ННYYYYMMDD_HHMMSS", для шифрования и отправки в НБКИ;
- файл выгрузки имеет кодировку ANSI;
- файл-комментарий выгрузки, содержит справочную информацию по выгрузке;
- общее хранилище файлов выгрузки: "./nbki_files";
- примеры файлов выгрузки: "./examples"

## Заключение
Решение представлено как есть и может быть адаптировано/доработано под актуальный стандарт и собственные нужды