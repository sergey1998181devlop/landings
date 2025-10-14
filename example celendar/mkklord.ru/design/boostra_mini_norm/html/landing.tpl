<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <base href="{$config->root_url}/"/>
    <title>{$meta_title|escape}</title>

    {* Метатеги *}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="title" content="{$meta_title2|escape}" />
    <meta name="description" content="{$meta_description|escape}" />
    <meta name="keywords"    content="{$meta_keywords|escape}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* Канонический адрес страницы *}
    {if isset($canonical)}<link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="{$config->root_url}" />
    <meta property="og:site_name" content="boostra" />
    <meta property="og:image" content="design/{$settings->theme|escape}/img/favicon.png" />

    <link rel="shortcut icon" type="image/ico" href="/design/boostra_mini_norm/assets/image/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$config->org_name}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:200,400,700,900" rel="stylesheet" />
    <link rel="stylesheet" crossorigin href="design/{$settings->theme|escape}/css/mkkforint.css?v=1.004">
    <link rel="stylesheet" crossorigin href="design/{$settings->theme|escape}/css/mkkforint_media.css?v=1.002">
</head>
<body>
<header>
    <div class="container">
        <div class="flex justify-between text-center py-4">
            {include 'block/logo.tpl'}
            <nav class="text-gray-600 font-semibold">
                <ul class="h-full flex justify-end items-center">
                    <li class="hidden lg:inline-block mr-4">
                        <p>88003333073</p>
                        <p>info@boostra.ru</p>
                    </li>
                    <li class="hidden lg:inline-block mr-4"><a class="inline-block no-underline" href="#about">О сервисе</a></li>
                    <li class="hidden lg:inline-block mr-4"><a class="inline-block no-underline" href="#documents">Документы</a></li>
                    <li><a class="hidden lg:inline-block no-underline mr-4" href="#steps">Как получить займ?</a></li>
                    <li><a class="hidden lg:inline-block no-underline" href="/user/login">Войти</a></li>
                    <li><a class="inline-block lg:hidden no-underline" href="/init_user?amount=30000&period=16">Регистрация</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>
<main>
    {$content}
    <footer class="aqua-footer">
        <div class="aqua-footer-top bg-gray-100">
            <div class="container">
                <div class="footer-top-block">
                    <nav>
                        <ul class="flex">
                            <li class="mr-4"><a href="#about">О сервисе</a></li>
                            <li class="mr-4"><a href="#documents">Документы</a></li>
                            <li class="mr-4"><a href="#steps">Как получить займ?</a></li>
                            <li class=""><a href="/user/login">Войти</a></li>
                        </ul>
                    </nav>
                    <div class="footer-payment-systems">
                        <img src="/design/{$settings->theme|escape}/assets/image/payment-systems/ps-1.png">
                        <img src="/design/{$settings->theme|escape}/assets/image/payment-systems/ps-2.png">
                        <img src="/design/{$settings->theme|escape}/assets/image/payment-systems/ps-3.png">
                        <img src="/design/{$settings->theme|escape}/assets/image/payment-systems/ps-4.png">
                    </div>
                </div>
                <div class="footer-company">
                    <div class="logo">
                        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 50 50" width="50px" height="50px" class="fill-current"><path d="M 17 5 C 14.250484 5 12 7.2504839 12 10 L 12 12 L 10 12 C 7.2504839 12 5 14.250484 5 17 L 5 40 C 5 42.749516 7.2504839 45 10 45 L 33 45 C 35.749516 45 38 42.749516 38 40 L 38 38 L 40 38 C 42.749516 38 45 35.749516 45 33 L 45 10 C 45 7.2504839 42.749516 5 40 5 L 17 5 z M 17 7 L 40 7 C 41.668484 7 43 8.3315161 43 10 L 43 33 C 43 34.668484 41.668484 36 40 36 L 38 36 L 36 36 L 17 36 C 15.331516 36 14 34.668484 14 33 L 14 23 C 14 21.883334 14.883334 21 16 21 L 19 21 L 19 27 C 19 29.197334 20.802666 31 23 31 L 29 31 L 31 31 L 34 31 C 36.197334 31 38 29.197334 38 27 L 38 17 C 38 14.250484 35.749516 12 33 12 L 14 12 L 14 10 C 14 8.3315161 15.331516 7 17 7 z M 10 14 L 12 14 L 14 14 L 33 14 C 34.668484 14 36 15.331516 36 17 L 36 27 C 36 28.116666 35.116666 29 34 29 L 31 29 L 31 23 C 31 20.802666 29.197334 19 27 19 L 21 19 L 19 19 L 16 19 C 13.802666 19 12 20.802666 12 23 L 12 33 C 12 35.749516 14.250484 38 17 38 L 36 38 L 36 40 C 36 41.668484 34.668484 43 33 43 L 10 43 C 8.3315161 43 7 41.668484 7 40 L 7 17 C 7 15.331516 8.3315161 14 10 14 z M 21 21 L 27 21 C 28.116666 21 29 21.883334 29 23 L 29 29 L 23 29 C 21.883334 29 21 28.116666 21 27 L 21 21 z"/></svg>
                        <span>{$config->org_name}</span>
                    </div>
                    <div class="divider"></div>
                    <div class="footer-company__name">© {$smarty.now|date_format:"%Y"} МКК «{$config->org_name}»</div>
                </div>
            </div>
        </div>
        <section class="section section-company-info ">
            <div class="container">
                <p>Навигация</p>
                <ul>
                    <li>
                        <a href="/sitemap">Карта сайта</a>
                    </li>
                </ul>
                <br>
                <p><b>Общество с ограниченной ответственностью Микрокредитная компания «{$config->org_name}» (сокращенно – ООО МКК «{$config->org_name}»). <br> ООО МКК «Лорд» использует товарный знак «Boostra» (номер и дата регистрации –
                        575895 от 24.05.2016) на основании соглашения об использовании товарного знака,
                        заключенного с ООО «Финтех-Маркет» (ИНН 6317164496)
                    </b></p>
                {*                <p>Email для приема обращений - <a href="mailto:mkk.finlab@inbox.ru">mkk.finlab@inbox.ru</a></p>*}
                {*                <p>Телефон для приема обращений - <a href="tel:+79014682292">+79014682292</a></p>*}
                <p>Юридический адрес - {$config->org_legal_address}</p>
                <p>Почтовый адрес - {$config->org_post_address}</p>
                <p>ИНН - {$config->org_inn}</p>
                <p>КПП - {$config->org_kpp}</p>
                <p>ОГРН - {$config->org_ogrn}</p>

                <br>
                <p><a href="/share_files/docs/режим_работы_и_обособленные_подразделения.pdf">Режим работы и обособленные подразделения.</a></p>
                <br>
                <p>Регистрационный № записи в государственном реестре МФО Банка России: №{$config->org_number_register_cb} от {$config->org_date_register_cb}</p>
                <br>
                <p>Член СРО «Союз «Микрофинансовый альянс «Институты развития малого и среднего бизнеса»,
                    Протокол Совета Союза № 20/24 от 27.09.2024, реестровая запись № 09 24 030 77 2183 .
                </p>
                <p>Адрес СРО «Союз «Микрофинансовый альянс «Институты развития малого и среднего бизнеса»:
                    125367, г. Москва, Полесский проезд 16, стр.1, оф. 308.
                </p>
                <p>Официальный сайт СРО - <a href="https://alliance-mfo.ru">alliance-mfo.ru</a> .</p>
                <br>

                <p>Официальный сайт Банка России - <a target="_blank" href="https://cbr.ru">https://cbr.ru</a></p>
                <p>Государственный реестр микрофинансовых организаций - <a target="_blank" href="https://cbr.ru/microfinance/registry">https://cbr.ru/microfinance/registry</a></p>
                <p>Интернет-приемная Банка России- <a target="_blank" href="https://cbr.ru/Reception">https://cbr.ru/Reception</a></p>
                <br>


                <p>Потребитель вправе направить обращение финансовому уполномоченному в соответствии со статьями 15-19 Федерального закона от 04 июня 2018 г. № 123-ФЗ «Об уполномоченном по правам потребителей финансовых
                    услуг». </p>

                <br>

                <p>Местонахождение и почтовый адрес уполномоченного: 119017, г. Москва, Старомонетный пер., д. 3.
                    Телефон: <a href="tel:88002000010">8 (800) 200-00-10</a>, адрес официального сайта: - <a target="_blank" href="https://finombudsman.ru">https://finombudsman.ru</a> .
                </p>

                <br>

                <p>Единоличный исполнительный орган (Генеральный директор) – {$organization->director},
                    с 18.09.2024 г.
                </p>

                <br>
                <p><a href="/share_files/docs/текст_для_страницы_информация_о_структуре_и_составе%20акционеров.pdf" target="_blank">Информация о структуре и составе акционеров ООО МКК «Лорд» .</a>
                </p>
                <p><a href="/share_files/docs/режим_работы_и_обособленные_подразделения.pdf">Информация о графике работе ООО МКК «Лорд» и обособленных подразделений.</a>
                </p>

                <br>
                <p><a href="#documents">Документы</a> ООО МКК «Лорд» .</p>

                <br>
                <p>При оформлении первого займа со сроком пользования до 5 дней (включительно) - пользование займом бесплатно*.</p>

                <br>
                <p>*100% скидка на проценты за пользование займом предоставляются при выполнении соответствующих условий в соответствии с положением Акции «Заем под 0%», а именно: погашение займа должно быть осуществлено
                    в изначально установленный условиями договора займа срок (без просрочки); продление срока возврата займа (пролонгация) не допускается.</p>
                <br>
            </div>
        </section>
    </footer>
</main>
<script src="/design/{$settings->theme|escape}/js/slider.js?v=1.01"></script>
<script src="/design/{$settings->theme|escape}/js/timer.js"></script>
<script>
    window.onload = function () {
        const MAX_AMOUNT = 30000; // Лимит суммы
        const MAX_PERIOD = 16; // Лимит периода

        const requestButton = document.getElementById('request-loan-btn');
        if (requestButton) {
            requestButton.addEventListener('click', function () {
                // Находим значение суммы
                const amountElement = document.querySelector('.calculator-item.slider-container:first-child .slider-current-value');
                // Находим значение периода (2-й блок)
                const periodElement = document.querySelector('.calculator-item.slider-container:nth-child(2) .slider-current-value');

                if (amountElement && periodElement) {
                    // Извлекаем значения и удаляем лишние пробелы
                    let amount = amountElement.textContent.replace(/\s+/g, '').trim(); // Удаляем пробелы
                    let period = periodElement.textContent.replace(/\s+/g, '').trim(); // Удаляем пробелы
                    // Формируем URL
                    // Проверяем лимиты
                    if (amount > MAX_AMOUNT) {
                        amount = MAX_AMOUNT; // Ограничиваем сумму
                    }

                    if (period > MAX_PERIOD) {
                        period = MAX_PERIOD; // Ограничиваем срок
                    }
                    {literal}
                    const url = `/init_user?amount=${amount}&period=${period}`;
                    // Перенаправляем пользователя
                    window.location.href = url;
                    {/literal}
                } else {
                    console.error('Не удалось найти элементы для amount или period.');
                }
            });
        } else {
            console.error('Кнопка "Оформить заявку" не найдена.');
        }
    };

</script>
</body>
</html>
