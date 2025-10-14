{if $is_developer}
    <div class="alert alert-success mb-0 rounded-0" role="alert">
        <b>DEVELOPER MODE</b> {if $user}<span>user_uid: {$user->uid}</span>{/if}
    </div>
{/if}

{if $is_admin || $is_looker}
    <div class="alert alert-danger mb-0 rounded-0" role="alert">
        <b>ADMIN MODE</b>
    </div>
{/if}

{if $settings->site_warning_message_enabled && $settings->site_warning_message_enabled_main_page}
    <div class="alert alert-danger mb-0 rounded-0 text-center" role="alert">
        <strong>{$settings->site_warning_message|nl2br}</strong>
    </div>
{/if}

{*{if $module !== 'CarDepositView'}*}
{*    <div id="car-deposit-popup">*}
{*        <div class="content">*}
{*            <span>«АвтоЗайм» до 500 000 ₽. ПТС и машина остаются у вас.</span>*}
{*            <a class="btn btn-primary py-0" href="/car_deposit">Оставить заявку</a>*}
{*        </div>*}
{*        <div id="car-deposit-popup-close" class="close">&times;</div>*}
{*    </div>*}
{*{/if}*}

<header class="bg-white py-2 mb-md-5 mb-4">
    <nav class="navbar bg-white">
        <div class="container">
            <div
                class="row w-100 row-cols-auto gy-md-2 gy-xl-0 align-items-center justify-content-between d-none d-md-flex">
                <div class="col-xl-2 col">
                    {include file='design/boostra_mini_norm/html/block/logo.tpl'}
                </div>
                <div class="col-xl-auto col">
                    <div class="d-flex mb-0 align-items-center gap-3">
                        {*}<div><a class="text-decoration-none text-dark" href="info_partners">Партнеры</a></div>{*}
{*                        <div><a class="text-decoration-none text-dark" href="info#demands">Условия</a></div>*}
{*                        <div><a class="text-decoration-none text-dark" href="/contacts">Контакты</a></div>*}
{*                        <div><a class="text-decoration-none text-dark" href="faq">Вопросы и ответы</a></div>*}
                    </div>
                </div>
{*                {if $settings->header_email_block}*}
{*                <div class="col-xl-auto col">*}
{*                    <p class="font-size-extra-small lh-sm mb-0">Нажмите, чтобы направить обращение</p>*}
{*                    <a class="text-warning text-decoration-none" href="mailto:{$settings->header_email|escape}">{$settings->header_email|escape}</a>*}
{*                    <p class="font-size-extra-small lh-sm mb-0">Электронная почта <br />для обращений</p>*}
{*                    <a class="lh-sm" href="/complaint" style="font-size: 11px; color: red; width: 100%; text-transform: uppercase; text-decoration: none;">Пожаловаться</a>*}
{*                </div>*}
{*                {/if}*}
{*                <div class="col-xl-auto col">*}
{*                    <div class="d-flex flex-column">*}
{*                        <div><a class="text-warning text-decoration-none" href="tel:88003333073">8 800 333 30 73</a></div>*}
{*                        <div><a class="text-warning text-decoration-none" href="tel:88003330534">8 800 333 05 34</a></div>*}
{*                        <div><p class="font-size-extra-small lh-sm mb-0">Клиентский сервис <br />Время работы: круглосуточно</p></div>*}
{*                    </div>*}
{*                </div>*}
                <div class="d-flex flex-column align-items-center gap-2 col-xl-auto col">
                    {include 'user_lk_menu.tpl'}
                    <div class="btn-group">
                        <div class="dropdown">
                            <button type="button" class="btn py-0 btn-outline-primary " id="dropdownMenuLogin"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <small>Внести платеж</small>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLogin">
                                {*}<li><a class="dropdown-item text-warning font-size-small py-0"
                                        href="user/passport">Оплата по цессии</a></li>{*}
                                <li><a class="dropdown-item text-warning font-size-small py-0"
                                        href="user/contract">Оплата по договору</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row w-100 justify-content-between mx-auto d-flex d-md-none">
                <div hidden class="col-auto px-0">
                    <button class="border-0 navbar-toggler collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarMenu" aria-controls="navbarSupportedContent" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="col">
                    {include 'design/boostra_mini_norm/html/block/logo.tpl'}
                </div>
                {include 'user_lk_menu.tpl' mobile_menu=true}
                <div class="btn-group" style="justify-content: end;padding: 0;">
                    <div class="dropdown">
                        <button type="button" class="btn py-0 btn-outline-primary " id="dropdownMenuLogin"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <small>Внести платеж</small>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLogin">
                            {*}<li><a class="dropdown-item text-warning font-size-small py-0"
                                    href="user/passport">Оплата по цессии</a></li>{*}
                            <li><a class="dropdown-item text-warning font-size-small py-0"
                                   href="user/contract">Оплата по договору</a></li>
                        </ul>
                    </div>
                </div>
{*                <span style="text-transform: uppercase;font-size: 11px;color: #000;line-height: 3;display: block;">Маркетплейс финансовых продуктов</span>            *}
            </div>
            <div hidden class="collapse navbar-collapse" id="navbarMenu">
                {*<p class="fw-bold mb-0 mt-3 w-75">Есть предложения как улучшить работу службы взыскания?</p>
                <p class="font-size-small mb-0"><small>Позвоните руководителю!</small></p>
                <a class="text-warning text-decoration-none d-block fw-bold mb-3"
                    href="tel:89310094643">8-931-009-46-43</a>*}
                {*}
                <a class="text-warning d-block fw-bold mb-2" href="user/passport">Оплата по цессии</a>
                <a class="text-warning d-block fw-bold mb-3" href="user/contract">Оплата по договору</a>
                {*}
                <div class="row row-cols-1 gy-3 mb-5 mt-1">
                    {*}<div class="col"><a class="font-size-small text-decoration-none text-dark"
                            href="info_partners">Партнеры</a></div>{*}
{*                    <div class="col"><a class="font-size-small text-decoration-none text-dark"*}
{*                            href="info#demands">Условия</a></div>*}
{*                    <div class="col"><a class="font-size-small text-decoration-none text-dark"*}
{*                            href="/contacts">Контакты</a></div>*}
                    <div hidden class="col"><a class="font-size-small text-decoration-none text-dark"
                            href="faq">Вопросы и ответы</a></div>
                </div>
                <div class="row align-items-end lh-1">
                    <div class="col-auto">
                        <p class="font-size-extra-small">Время работы:<br /> круглосуточно</p>
                    </div>
                </div>
                <div class="lh-1">
                    <a class="" href="https://telegram.me/boostra_bot" target="_blank"><img
                            src="design/boostra_mini_norm/img/tg-48.png" /></a>
                    <a class="" href="https://watbot.ru/w/mjj" target="_blank"><img
                            src="design/boostra_mini_norm/img/viber-48.png" /></a>
                    <a class="" href="https://vk.com/write-212426324" target="_blank"><img
                            src="design/boostra_mini_norm/img/vk-48.png" /></a>
                    <a class="" href="https://watbot.ru/w/mji" target="_blank"><img
                            src="design/boostra_mini_norm/img/whatsapp-48.png" /></a>
                </div>
            </div>
        </div>
    </nav>
</header>