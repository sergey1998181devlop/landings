<div id="sitemap_container">
    <h1>Карта сайта</h1>

    <ul class="sitemap_list">
        {foreach $routes as $route => $description}
            <li>
                <a href="{$route}" class="sitemap_link">{$description}</a>
            </li>
        {/foreach}
    </ul>
</div>

<style>
    /* Стиль для контейнера карты сайта */
    #sitemap_container {
        /*max-width: 800px; !* Ограничиваем ширину для лучшей читаемости *!*/
        /*margin: 20px auto; !* Выравниваем по центру и добавляем вертикальные отступы *!*/
        padding: 20px; /* Внутренние отступы */
        background-color: #fff; /* Белый фон */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Небольшая тень */
        border-radius: 8px; /* Скругленные углы */
    }

    /* Стиль для заголовка h1 */
    #sitemap_container h1 {
        /*text-align: center; !* Выравниваем заголовок по центру *!*/
        margin-bottom: 30px; /* Отступ снизу */
        color: #333; /* Цвет заголовка */
    }

    /* Стиль для основного списка */
    .sitemap_list {
        list-style: none; /* Убираем стандартные маркеры списка (точки) */
        padding: 0; /* Убираем внутренний отступ списка по умолчанию */
        margin: 0; /* Убираем внешний отступ списка по умолчанию */
    }

    /* Стиль для каждого элемента списка (каждой ссылки) */
    .sitemap_list li {
        margin-bottom: 10px; /* Добавляем отступ между элементами списка */
        padding-bottom: 10px; /* Добавляем нижний отступ */
        border-bottom: 1px solid #eee; /* Добавляем тонкую разделительную линию */
        /* Убираем последнюю линию */
        &:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    }

    /* Стиль для самих ссылок */
    .sitemap_list li a.sitemap_link {
        text-decoration: none; /* Убираем стандартное подчеркивание */
        color: #007bff; /* Цвет ссылки (например, стандартный синий Bootstrap) */
        font-size: 1.1em; /* Увеличиваем размер шрифта */
        display: block; /* Делаем ссылку блочным элементом, чтобы она занимала всю ширину li */
        padding: 5px 0; /* Небольшой внутренний отступ */
        transition: color 0.2s ease; /* Плавное изменение цвета при наведении */
    }

    /* Стиль ссылки при наведении */
    .sitemap_list li a.sitemap_link:hover {
        color: #0056b3; /* Цвет ссылки при наведении */
        text-decoration: underline; /* Подчеркивание при наведении */
    }
</style>