<div class="panel">
    {if $restricted_mode === 1 && (in_array($due_days, [1,2])) && $due_days !== 'not'}
        <div class="restrict_alert row" style="width: 400px;">
            <div class="col-md-2 hidden-xs">
                <img src="design/{$settings->theme|escape}/img/restrict/alert1.png">
            </div>
            <div class="col-md-8">
                Мы подготовили для Вас заём с увеличенной суммой.
                Предлагаем Вам воспользоваться <span style="color: #684A2D; text-decoration: underline">уникальным предложением</span> для постоянных клиентов, которые ценят своё время и деньги.
                <b>Спешите, осталось всего 13 предодобренных займов!</b>
                {foreach $all_orders as $key => $orders_data}
                    {foreach $orders_data as $order_data}
                        {if $order_data->balance->zaim_number != null}
                            {if $order_data->order->additional_service_repayment}
                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                    <input type="hidden" name="tv_medical" value="1"/>
                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $vita_med->price + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                {else}
                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                {/if}
                            {else}
                                {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                            {/if}
                            <br>
                            <div class="restrict_loan_info">
                                <div class="float_left_block" style="margin-right: 50px;">
                                    <p>Номер договора</p>
                                    <h3>{$order_data->balance->zaim_number}</h3>
                                </div>
                                <div class="float_left_block">
                                    <p>Сумма долга</p>
                                    <h3>{$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty} руб.</h3>
                                </div>
                                <div class="clear"></div>
                                <div>
                                    <form method="POST" action="user/payment" class="user_payment_form" style="margin: 0;">
                                        <div class="action">
                                            {if $order_data->order->additional_service_repayment}
                                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                                    <input type="hidden" name="tv_medical" value="1"/>
                                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                                {/if}
                                            {/if}
                                            <input type="hidden" name="amthash" value="{base64_encode($amount_value)}">
                                            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                                            <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>
                                            <input style="display:none" class="payment_amount"
                                                   data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                                                   name="amount"
                                                   value="{$amount_value}"
                                                   max="{$amount_value}" min="1"/>
                                            <button class="restrict_button" data-user="{$user->id}"
                                                    data-event="4" type="submit">Погасить и воспользоваться предложением
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                {/foreach}
            </div>
        </div>
    {/if}
    <style type="text/css">
        .get_prolongation_modal {
            height: 54px;
        }

        .user_info {
            display: flex;
            gap: 5%;
            flex-direction: column;
        }

        .send_complaint {
            background-color: #888585;
        }

        .send_complaint:hover {
            background-color: #575252;
        }

        #company_form {
            border: 2px dashed #2c2b39;
            display: block;
            padding: 10px;
            border-radius: 10px;
            margin: 15px 0;
            background: #fcc512;
            max-width: 480px;
            text-align: center;
        }

        #private .tabs .content #company_form p {
            margin: 0;
            font-size: initial;
        }

        #company_form a {
            display: block;
            background: #2c2b39;
            color: white;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
        }

        @media screen and (max-width: 768px) {
            #private .tabs .content #company_form p {
                font-size: 12px;
            }
        }
        .logout_hint{
            border: 1px solid red;
            color: red;
            width: fit-content;
            padding: 10px;
            border-radius: 10px;
            margin-right: 10px;
        }

        .logout_hint a{
            color: blue;
            text-decoration: underline;
        }
        .carousel-container-banners {
            width: 100%;
            max-width: 800px;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-sizing: border-box;
            /*для промо*/
            padding: 0 !important;
            height: 290px !important;
        }

        .user_info-banner{
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }
        .carousel-container-banners .owl-carousel {
            height: 100%;
        }
        .carousel-container-banners .item {
            text-align: center;
            box-sizing: border-box;
            display: flex !important;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .carousel-container-banners .owl-stage {
            display: flex;
            flex-wrap: nowrap;
            transition: transform 0.5s ease-in-out;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .carousel-container-banners  .owl-nav {
            position: absolute;
            top: 45%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .carousel-container-banners .owl-nav button {
            pointer-events: all;
            background: lightgray !important;
            color: #fff;
            border: none;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel-container-banners  .owl-carousel .owl-item img {
            width: 100%;
            height: 100%;
        }
        .carousel-container-banners  .owl-stage-outer {
            height: 100%;
        }
        .carousel-container-banners .creditDoctorBanner {
            height: 250px;
        }
        .carousel-container-banners .additional_service__banner___text{
            padding: 0;
            justify-content: center;
        }
        .carousel-container-banners .additional_service__pay__credit__banner___details {
            margin: 0;
        }
        .carousel-container-banners .additional_service__banner___text .btn  {
            font-size: 18px;
        }
        .carousel-container-banners .about_promotion_div {
            margin: 0;
        }
        @media (max-width: 600px) {
            .carousel-container-banners .owl-carousel .item {
                padding: 5px;
            }
            .carousel-container-banners {
                height: 230px;
            }
            .carousel-container-banners .owl-nav button {
                width: 35px;
                height: 35px;
            }
            .owl-stage-outer{
                width: 85vw;
            }
        }

    </style>
    {if $isPremierBannerVisible === 'activate_banner'}
    <style>
        .banner img {
            width: auto !important;
            height: auto !important;
        }
        .banner {
            position: relative;
            background-color: #FFD700;
            padding: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .carousel-container {
            min-height: 340px;
        }
        .banner-container {
            width: 100%;
        }
        .banner-content {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .item .banner-container .banner-content .banner-logo {
            margin: 0 !important;
            padding: 10px 0 0 0 !important;
            line-height: unset !important;
        }
         .item .banner-container .banner-content .banner-text {
            font-size: 1rem !important;
            font-weight: 500;
            margin: 20px 0 0 0 !important;
            padding: 0;
        }

        .banner-medium-text {
            font-size: 1.3rem;
            margin: 10px 0;
            padding: 0;
            text-transform: uppercase;
            width: 70%;
            text-align: center;
        }

        .banner-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #000;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin: 10px 0;
            cursor: pointer;
        }

        .banner-link {
            color: #000;
            text-decoration: none;
            font-size: 0.8rem;
            display: block;
            margin: 5px 0;
            font-weight: 500;
        }

        .banner-1-img-1 {
            position: absolute;
            left: 0;
            bottom: 0;
        }
        .banner-1-img-2 {
            position: absolute;
            right: 100px;
            bottom: 15px;
        }
        .banner-1-img-3 {
            position: absolute;
            right: 40px;
            bottom: 60px;
        }

        @media (max-width: 992px) {
            .banner {
                max-width: 90%;
            }

            .banner-button {
                padding: 8px 16px;
            }
        }

        @media (max-width: 768px) {
            .banner {
                padding: 15px 0;
            }

            .banner-medium-text {
                font-size: 1rem;
            }

            .banner-button {
                padding: 8px 16px;
            }

            .banner-link {
                font-size: 0.7rem;
            }

            .banner-1-img-1 {
                display: none !important;
            }

            .banner-1-img-2 {
                right: 10px;
                bottom: 0;
            }

            .banner-1-img-3 {
                left: 10px;
                bottom: 0;
            }
        }

        @media (max-width: 576px) {
            .banner {
                padding: 10px 0;
                box-shadow: none;
                overflow: hidden;
            }

            .banner-content {
                padding: 0 10px;
            }

            .banner-medium-text {
                width: 100%;
            }

            .banner-button {
                padding: 6px 12px;
                margin: 6px 0;
            }

            .banner-link {
                font-size: 0.6rem;
                margin: 4px 0;
            }

            .banner-1-img-2 {
                right: -20px;
                bottom: 0;
            }

            .banner-1-img-3 {
                left: -10px;
                bottom: 0;
            }
        }
    </style>
    {/if}
    {if $isPremierBannerVisible === 'activated_banner'}
    <style>
        .banner {
            position: relative;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: url('design/{$settings->theme|escape}/img/premier_promo/banner-2-img-1.png') 100% 0 no-repeat;
        }
        .carousel-container {
            min-height: 340px;
        }
        .banner-container {
            width: 100%;
        }
        .banner-content {
            padding: 50px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background-color: #FFD700;
            width: 70%;
            border-radius: 10px;
        }
        .item .banner-container .banner-content .banner-logo {
            margin: 0 !important;
            padding: 10px 0 !important;
            line-height: unset !important;
        }
        .item .banner-container .banner-content .banner-text {
            font-size: 1rem !important;
            font-weight: 500;
            margin: 20px 0 0 0 !important;
            padding: 0;
        }

        .banner-medium-text {
            font-size: 1.2rem;
            margin: 10px 0;
            padding: 0;
            text-transform: uppercase;
            text-align: center;
            font-weight: 600;
        }

        .banner-medium-text-link {
            color: #000;
            text-decoration: none;
        }

        .banner-button {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            position: relative;
            background: #fff;
            color: #000;
            border-radius: 40px;
            cursor: pointer;
            padding: 15px 60px;
        }

        .banner-button p {
            margin: 0 !important;
            padding: 0 !important;
        }

        .banner-button .promocode {
            font-size: 1.5rem;
            font-weight: 700;
            color: #000;
        }

        .banner-button .icon, .banner-button .thumbs-up {
            position: absolute;
            right: 20px;
            top: 40%;
        }

        .banner-link {
            color: #000;
            text-decoration: none;
            font-size: 1rem;
            display: block;
            margin: 5px 0;
            font-weight: 500;
        }

        .banner-2-img-1 {
            position: absolute;
            right: 0;
            bottom: 0;
        }

        @media (max-width: 1200px) {
            .banner {
                width: 90%;
            }
        }

        @media (max-width: 768px) {
            .banner {
                background: none;
            }

            .banner-content {
                padding: 15px;
                width: 100%;
            }

            .banner-medium-text {
                font-size: 1.6rem;
            }

            .banner-button {
                padding: 10px 60px;
            }

            .banner-button p:first-child {
                font-size: 1.4rem;
            }

            .item .banner-container .banner-content .banner-logo {
                text-align: center !important;
                padding: 0 !important;
            }

            .item .banner-container .banner-content .banner-logo img {
                width: 80% !important;
                display: inline !important;
            }

            .banner-link {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .banner-link {
                font-size: 1.2rem;
            }

            .banner-content {
                padding: 10px;
            }

            .banner-medium-text {
                font-size: 1.2rem;
            }

            .banner-button {
                padding 8px 40px;
            }

            .banner-button p:first-child {
                font-size: 1.1rem;
            }

            .banner-button .promocode {
                font-size: 1.2rem;
            }

            .banner-button .icon {
                right: 10px;
            }
        }
    </style>
    {/if}

    <div class="user_info">
        <div class="user_info-banner">
            <h1>{$salute|escape}</h1>
{*            {if $user->balance->zaim_number != 'Нет открытых договоров'}*}
{*                {if $payCredit || !$existTg || $sbp_attach || in_array($isPremierBannerVisible, ['activate_banner', 'activated_banner'])}*}
{*                <div class="carousel-container-banners">*}
{*                    <div class="owl-carousel">*}
{*                        {if $isPremierBannerVisible === 'activate_banner'}*}
{*                            <div class="item">*}
{*                                {include 'premier_promo/includes/banner-1.tpl' giftOrderId=$promoGift->contract_number}*}
{*                            </div>*}
{*                        {/if}*}
{*                        {if $isPremierBannerVisible === 'activated_banner'}*}
{*                            <div class="item">*}
{*                                {include 'premier_promo/includes/banner-2.tpl' code=$promocode}*}
{*                            </div>*}
{*                        {/if}*}
{*                        {if !$existTg}*}
{*                            <div class="item">*}
{*                                {include*}
{*                                file='partials/telegram_banner.tpl'*}
{*                                margin='0 0 16px 0'*}
{*                                source='lk'*}
{*                                tg_banner_text='<h3>Подпишись на наш Telegram канал <br> И получай выгодные предложения одним из первых</h3>'*}
{*                                phone={{$user->phone_mobile}}}*}
{*                            </div>*}
{*                        {/if}*}
{*                        {if $restricted_mode !== 1 && $sbp_attach == true}*}
{*                            <div class="item">*}
{*                                {include 'attach_sbp.tpl'}*}
{*                            </div>*}
{*                        {/if}*}
{*                    </div>*}
{*                </div>*}
{*                {/if} *}
{*            {/if}*}
        </div>
        <input type="hidden" value="{$full_payment_amount_done}" id="full_payment_amount_done">
    </div>

        {if $cross_orders && $cross_orders_up}
            {foreach $cross_orders as $cross_order}
                {view_order current_order=$cross_order}
            {/foreach}                                
        {/if}                        

    {if $restricted_mode_logout_hint === 1}
        <div class="logout_hint">
            <span>Личный кабинет работает в ограниченном режиме.</span>
            <br>
            <span>Для взятия нового займа нажмите </span>
            <a href="/user/logout">Перезайти</a>
        </div>
    {/if}

    {if $restricted_mode !== 1}
        {if ($user->gender == 'female') && ($smarty.now|date_format:"%Y%m%d" < '20240311') }{* Поздравление с 8 марта*}
            {include 'block/8march.tpl'}
        {/if}

        {assign var="date_finish" value="2024-01-09 0:00:00"}
        {if ($smarty.now < strtotime($date_finish))}
            <style type="text/css">
                #info_banner {
                    color: #664d03;
                    background-color: #fff3cd;
                    border-color: #ffecb5;
                    max-width: 685px;
                    padding: 1rem 1rem;
                    margin-bottom: 1rem;
                    border: 1px solid transparent;
                    border-radius: 0.25rem;
                    margin-bottom: 3rem;
                }
            </style>
            <div id="info_banner">
                Уважаемые Клиенты, просим учитывать, что при осуществлении оплаты Договора займа по <strong>РЕКВИЗИТАМ</strong> с 29.12.2023 г. по 08.01.2024 г., денежные средства будут проведены в счет оплаты задолженности не ранее 09.01.2024 г.
            </div>
        {/if}

        {*    {include 'motivation_banner.tpl'}*}

        {if $loan_buyers }
            {foreach $loan_buyers as $loan_buyer}
                <p>Уведомляем Вас, что задолженность по договору займа № {$loan_buyer['loan_number']}
                    от {$loan_buyer['loan_date']} г. передана {$loan_buyer['loan_buy_date']} по агентскому договору в
                    пользу {$loan_buyer['loan_buyer_name']} с целью возврата просроченной задолженности.</p>
            {/foreach}
        {/if}


        {if !$collapse_rating_banner}
            {include 'credit_rating/credit_rating.tpl'}
        {/if}

        {if $indulgensia}
            <h3 class="green" style="line-height:1.2;font-weight:normal;margin-bottom:1rem">
                Прощаем вам все начисления и проценты по займу, верните ровно столько, сколько брали!
                <br />
                Акция действительна только 3 дня с 17 по 19 ноября включительно.
                <br />
                Оплатите через кнопку "Оплатить другую сумму", позвоните на <a href="tel:88003333073">88003333073</a>

                и мы закроем ваш долг!
            </h3>
        {/if}

    {if Helpers::isFilesRequired($user) && !$user->file_uploaded && (!$user->balance->zaim_number || $user->balance->zaim_number=='Нет открытых договоров')}
        <div class="files">
            <p>
                Прикрепите фотографии с лицом и паспортом для подтверждения
            </p>
            <a href="user/upload" class="button medium"> Добавить</a>
        </div>
    {/if}

    {/if}

    {if $restricted_mode_logout_hint !== 1}
        {if $all_orders}
            {include 'user_current_divide_orders.tpl' divide_order=$all_orders last_order=null exitpool_completed=true}
        {/if}

        {if $divide_order}
            {include 'user_current_divide_orders.tpl'}
        {else}
            {include 'user_current_loan.tpl'}
        {/if}
    {/if}


    {if $restricted_mode !== 1}

        {if $user->cdoctor_pdf}
            <div class="cdoctor-file" style="background:url(design/{$settings->theme|escape}/img/cdoctor.svg) right center no-repeat">
                <div class="cdoctor-file-left">
                    <div class="cdoctor-file-title">Зачем ждать, когда можно действовать?</div>
                    <div class="cdoctor-file-info">Мы уже получили Вашу кредитную историю и принимаем решение. Узнайте больше.</div>
                    <a href="{$user->cdoctor_pdf}" target="_blank" class="button medium">Узнать кредитную историю</a>
                </div>
                <div class="cdoctor-file-left">
                </div>
            </div>
        {/if}

    {if $show_company_form && $config->available_company_btn_form}
        <div id="company_form">
            <a href="{$config->root_url}/company_form">Займ для ИП и ООО</a>
            <p>(для индивидуальных предпринимателей и юридических лиц)</p>
        </div>
    {/if}

    {if !in_array($user->order['status'], [8, 9, 10, 11, 13]) && $user->order['1c_status'] == '3.Одобрено'}
        {include 'promocode.tpl'}
    {/if}

        {if $autoapprove_card_reassign || $is_need_choose_card}
            <p class="autoapprove_card_security">
                <span class="autoapprove_card_security__title">Важно! Мы повысили уровень безопасности Ваших персональных данных.</span>
                <br>Для дальнейшего совершения операций с денежными средствами необходимо перепривязать Вашу карту.
                <br>Добавьте дебетовую (обычную) карту. На неё мы продолжим зачислять Вам деньги.
                <br>При необходимости Вы можете добавить более одной карты и выбирать любую из них для зачисления/списания средств.
            </p>
        {/if}

        {if $is_user_order_taken}
            {if $settings->banner_url_a && $settings->banner_url_b}
                {if round(($settings->banner_clicks_a/($settings->banner_clicks_a + $settings->banner_clicks_b))*100) >= rand(1,100)}
                    <a class="likezaim_banner" href="{$settings->banner_url_a}" target="_blank">
                        <img src="/design/boostra_mini_norm/img/banners/likezaim.png" alt="likezaim" />
                    </a>
                {else}
                    <a class="likezaim_banner" href="{$settings->banner_url_b}" target="_blank">
                        <img src="/design/boostra_mini_norm/img/banners/marketplace.png" alt="marketplace" />
                    </a>
                {/if}
            {/if}
        {elseif $likezaim}
            {if $likezaim->link}
                <a class="likezaim_banner" href="{$likezaim->link}" target="_blank" >
                    <img class="likezaim_banner" src="/design/boostra_mini_norm/img/banners/likezaim.png" alt="likezaim classic" />
                </a>
            {/if}
        {/if}

        {*}
        <div class="spoiler">
            <button class="spoiler-button" type="button">
                <i class="bi bi-plus-square"></i>
                <i class="bi bi-dash-square"></i>
                <span>Внимание!!!</span>
            </button>
            <div class="spoiler-content">
                <div><strong>Уважаемые клиенты, пожалуйста, будьте внимательны и остерегайтесь мошенников!</strong>
                    <br />Наша компания никогда не предлагает оплачивать обязательства переводом на карту. Если вы
                    получаете такие предложения, это может быть попытка обмана. Оплата возможна безопасно:
                </div>
                <ul>
                    <li>Из личного кабинета заемщика</li>
                    <li>Из мобильного приложения</li>
                    <li>В любом отделении Почты РФ (бесплатно)</li>
                    <li>По реквизитам из договора займа (пункт № 8)</li>
                </ul>
                <div>Уважаемые клиенты, пожалуйста, при переводе по реквизитам из п. № 8 договора займа, обязательно
                    указывайте номер договора, чтобы мы могли быстрее обработать ваш платеж!</div>
            </div>
        </div>
        {literal}
            <style>
                .spoiler {
                    margin: 30px 0 0 0;
                    background: #fff;
                    border-radius: 4px;
                    transition: all .4s;
                    border: 1px solid rgba(0,0,0,.2);
                }
                .spoiler .bi-dash-square, .spoiler.opened .bi-plus-square {
                    display: none;
                }
                .spoiler.opened {
                    background: #2196F3;
                    border: 1px solid #2196F3;
                }
                .spoiler.opened .spoiler-button {
                    color: #fff;
                }
                .spoiler.opened .bi-dash-square {
                    display: block;
                }
                .spoiler:hover i.bi {
                    opacity: 1;
                }

                .spoiler-content {
                    display: none;
                    overflow: hidden;
                    border-top: 1px solid rgba(255,255,255,.5);
                    padding: 20px;
                    color: #fff;
                    font-size: 14px;
                    letter-spacing: 0.5px;
                }

                .spoiler-button {
                    width: 100%;
                    justify-content: flex-start;
                    display: flex;
                    align-items: center;
                    box-shadow: none;
                    gap: 10px;
                    padding: 15px 20px;
                    border: none;
                    background: none;
                    color: #222;
                    cursor: pointer;
                    border-radius: 5px;
                    font-size: 20px;
                    transition: all .1s;
                }

                .spoiler-button:hover {
                    background: none;
                    box-shadow: -5px 5px 1rem rgba(0, 0, 0, .2);
                }

                .spoiler-button i.bi {
                    opacity: .5;
                    transition: all .1s;
                }
            </style>
            <script>
                $(document).ready(function() {
                    $(".spoiler-button").on("click", function() {
                        const content = $(this).next(".spoiler-content");
                        const wrap = $(this).parent('.spoiler');
                        content.slideToggle(300);
                        wrap.toggleClass('opened');
                    });
                });
            </script>
        {/literal}
        {*}
        <div class="cards">
            {if $cards}
            <div class="about">
                <a style="text-decoration:underline" href="javascript:void(0);" class="js-toggle-cards toggle-cards" style="">Мои карты</a>
            </div>
            <div class="js-cards " style="display:none">
                <div class="split">
                    <input type="hidden" class="card-user-id" value="{$user->id}">
                    <ul id="card_list">
                        {foreach $cards as $card}
                            {if ((!$user->use_b2p && $card->rebill_id) || $user->use_b2p)}
                                <li data-card-id="{$card->id}">
                                    <div>
                                        {*                                    {if $basicCard == $card->id}*}
                                        {*                                        <input type="radio"  checked="true" name="active_card" style="-webkit-appearance: auto;" data-card-id = "{$card->id}">*}
                                        {*                                    {else}*}
                                        {*                                        <input type="radio"  name="active_card" style="-webkit-appearance: auto;" data-card-id = "{$card->id}">*}
                                        {*                                    {/if}*}
                                        Номер карты: {$card->pan}

                                        &nbsp;
                                        {if isset($organizations[$card->organization_id])}
                                            ({$organizations[$card->organization_id]})
                                        {/if}
                                    {*}
                                    <a href="javascript:void(0);" class="toggle-link autodebit-link js-autodebit {if $card->autodebit}toggle-link-on js-detach{/if}" data-number="{$card->Pan}" data-card="{$card->id}" data-type="b2p" >
                                        <span>Автоплатеж</span>
                                    </a>
                                    {*}

                                    {if (!empty($order_for_choosing_card) && $order_for_choosing_card['card_id'] !== $card->id &&
                                    $cards|@count > 1 && !empty($order_for_choosing_card['have_close_credits']) &&
                                    $order_for_choosing_card['status'] == 2) && $order_for_choosing_card['organization_id'] == $card->organization_id}
                                        <a
                                                href="#"
                                                class="button small modal-choose_card"
                                                data-button-card-id="{$card->id}"
                                                data-button-order-id="{$order_for_choosing_card['id']}"
                                                style="margin: 10px 0 10px 10px; box-shadow: none; font-size: .9rem!important;"
                                        >Выбрать карту</a>
                                    {/if}

                                    {if (!$busy_cards[$card->id] && $cards|@count > 1)}
                                        <a
                                                href="#"
                                                class="button small modal-remove_card"
                                                data-button-card-id="{$card->id}"
                                                style="margin-left: 10px; box-shadow: none; font-size: .9rem!important;"
                                        >Удалить из ЛК</a>
                                    {else}

                                        {if $user->balance->zaim_number != 'Нет открытых договоров' && $card->id == $zaim_order->card_id}
                                            <p style="margin:0;font-size:15px;color:#21CA50  ">Используется для
                                                займа {$user->balance->zaim_number}</p>
                                        {else}
                                            {if (!empty($last_order['1c_id']) && ($last_order['status'] != 3 &&  $last_order['status'] != 4 && $last_order['status'] != 5 && $last_order['status'] != 11) && $card->id == $last_order['card_id'])}
                                                <p style="margin:0;font-size:15px;color:#21CA50  ">Используется для
                                                    заявки {$last_order['1c_id']}</p>
                                            {/if}
                                        {/if}
                                    {/if}
                                </div>
                                <span></span>
                            </li>
                        {else}
                            <li style="color:red">
                                <div>Номер карты: {$card->pan}
                                    <div style="font-size:1rem;">Ошибка привязки карты. Пожалуйста привяжите карту повторно.</div>
                                </div>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
            <div id="removeCardModal" class="mfp-hide">
                <div class="removeCardModal-close">
                    <p onclick="$.magnificPopup.close();">X</p>
                </div>
                <h2 class="text-center">Уверены, что хотите удалить карту?</h2>
                <div id='removeCardModal-buttons'>
                    <button id="confirmRemove" class="action-remove_card">Да</button>
                    <button id="cancelRemove" onclick="$.magnificPopup.close();">Нет</button>
                </div>
            </div>
                
            <div id="chooseCardModal" class="mfp-hide modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Замена карты</h5>
                    </div>

                    <div class="modal-body">
                        {if isset($user->order) && $user->order.status|intval === 2}
                            <p>Заменить карту в одобренной заявке? После привязки потребуется повторная проверка.</p>
                        {else}
                            <p>Уверены, что хотите выбрать данную карту для получения займа?</p>
                        {/if}
                    </div>

                    <div class="modal-footer">
                        <button class="button button-inverse" onclick="$.magnificPopup.close()">Нет</button>

                        <button id="confirmChooseCard" class="action-choose_card">Да</button>
                    </div>
                </div>
            </div>
        {else}
            {if !$autoapprove_card_reassign}
                <h2 class="red">Важно! Мы повысили уровень безопасности Ваших персональных данных. </h2>
                <p class="green" style="margin-top:10px">
                    Для дальнейшего совершения операций с денежными средствами необходимо перепривязать Вашу карту.
                    <br />
                    Добавьте дебетовую (обычную) карту. На неё мы продолжим зачислять Вам деньги.
                    <br />
                    При необходимости Вы можете добавить более одной карты и выбирать любую из них для зачисления/списания средств.
                </p>
            {/if}
            <div class="nocards">Нет доступных карт</div>

        {/if}

        {if $autoapprove_card_reassign}
            <p style="color: #FF0000">Для получения одобренной заявки привяжите карту {$last_order_card->pan}</p>
        {/if}

        {$card_error}

        {if $settings->b2p_enabled || $user->use_b2p}
            <button id="myBtn" class="button medium" data-organization_id="{$organization_id}" style="margin-top:5px;">Добавить карту</button>
            {*                <a href="{$user->add_card}" class="button medium js-b2p-add-card" style="margin-top:5px;">Добавить карту</a>*}
        {elseif $user->add_card}
            <button id="myBtn" class="button medium" style="margin-top:5px;">Добавить карту</button>
            {*                <a href="{$user->add_card}" class="button medium" style="margin-top:5px;">Добавить карту</a>*}
        {/if}
        
        <link href="design/{$settings->theme}/css/add_card.css?v=1" rel="stylesheet" type="text/css" >
        {if Helpers::isFilesRequired($user)}
            <div id="myModal" class="add_card_photo_modal">
                <!-- Modal content -->
                <div class="modal-content add_photo">
                    <fieldset class="passport4-file file-block">

                        <legend>Фото карты</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                            {if $passport4_file}
                                <label class="file-label">
                                    <div class="file-label-image">
                                        <img src="{$passport4_file->name|resize:100:100}" />
                                    </div>
                                    {*<span class="js-remove-file" data-id="{$passport4_file->id}">Удалить</span>*}
                                    <input type="hidden" id="passport4" name="user_files[]" value="{$passport4_file->id}" />
                                </label>
                            {/if}
                        </div>

                        <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                            <div class="file-label">
                                <label for="user_file_passport4" class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/card_logo.svg" />
                                </label>
                                <label>
                                    <i style="font-size: 22px">Приложите фото Вашей именной банковской карты либо скриншот
                                        из личного кабинета банка, где видно полный номер и фамилия владельца.
                                        <strong>ВНИМАНИЕ! CVC-код нужно закрыть!</strong>
                                    </i>
                                </label>
                                <label onclick="sendMetric('reachGoal', 'get_user_photo_5');"  class="get_mobile_photo photo_btn not-visible-sm" for="user_file_passport4" >
                                    Сделать фото
                                </label>

                                <label onclick="sendMetric('reachGoal', 'download_user_photo_5');"  class="photo_btn" for="user_file_passport4">Загрузить фото</label>
                                <input data-preload type="file" id="user_file_passport4" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                            </div>
                        </div>
                    </fieldset>
                    <div>
                        <button class="button medium next-step-button" style="margin-top:5px;" disabled>Далее </button>
                    </div>
                </div>

            </div>
        {/if}
        </div>
    </div>

{*        {if $has_active_loans}*}
{*            <div style="padding: 30px 0 10px">*}
{*                <h2 style="margin-bottom: 20px;">Где безопасно скачать или обновить приложение Boostra</h2>*}
{*                <h3 style="margin-bottom: 20px;">Способ 1. В популярных магазинах приложений</h3>*}

{*                <div class="app_block">*}
{*                    <a href="https://redirect.appmetrica.yandex.com/serve/749593578275145650">*}
{*                        <img style="width:220px" src="design/{$settings->theme|escape}/img/nashstore_icon.png" alt="nashstore_icon"/>*}
{*                    </a>*}
{*                    <a href="https://redirect.appmetrica.yandex.com/serve/821651145620505260">*}
{*                        <img style="width:220px" src="design/{$settings->theme|escape}/img/rustore_icon.png" alt="rustore_icon"/>*}
{*                    </a>*}
{*                    <a href="https://redirect.appmetrica.yandex.com/serve/533644391370206393">*}
{*                        <img style="width:220px" src="design/{$settings->theme|escape}/img/appstore_icon.png" alt="appstore_icon"/>*}
{*                    </a>*}
{*                </div>*}

{*                <h3 style="margin-bottom: 20px;">Способ 2. Скачайте и установите наше приложение, напрямую по ссылке ниже</h3>*}
{*                <a class="button small button-inverse" target="_blank" href="https://redirect.appmetrica.yandex.com/serve/749593560105358068">Скачать для Android</a>*}
{*            </div>*}
{*        {/if}*}
        {* 
        {if ($banners_count > 0 && !$payCredit) ||  ($userGift->got_gift && !$payCredit)}
            <div class="partner-banners">
                <h2>Вам {if $banners_count == 1}доступно ПО от нашего партнёра{else}доступны ПО от наших партнёров{/if}</h2>
                {if $has_credit_doctor || ($userGift->got_gift && !$payCredit)}
                    {include file='credit_doctor_banner.tpl'}
                {/if}
            </div>
        {/if}
        *}

        {if $user->balance->zaim_number == 'Нет открытых договоров' && (!$user->order || $user->order['status'] == 3 || $user->order['status'] == 2)}
            <div class="remove_account_block">
                <a href="javascript:void(0);" data-modal_mf="confirm_remove_account">Удалить личный кабинет</a>
            </div>
            {include 'modals/modal_asp_contract_deleted_user_cabinet.tpl'}
        {/if}

    {/if}
    <input type="hidden" id="show-modal-asp" value="{$show_asp_modal}">
    <input type="hidden" id="input-is-admin" value="{$is_admin}">
    <input type="hidden" id="input-is-looker" value="{$is_looker}">
    <div id = 'div-show-asp-modal' style="display: none">
        <style>
            @font-face{
                font-family: 'Circe';
                font-weight: 400;

                src: url('../fonts/Circe.woff') format('woff'), url('../fonts/Circe.ttf');
            }
            .arbitration-modal-content {
              width: 30%;
              margin: 0 auto;
              padding: 20px;
              position: relative;
              background: #FFFFFF;
              box-shadow: 0 4px 10px 2px rgba(129, 129, 129, 0.25);
              border-radius: 15px;
              min-width: 200px;
              max-width: 400px;
            }
            .arbitration-modal-content [name="sms_asp"] {
              max-width: 100px;
            }
            #asp_sms {
                width: 30%;
                margin: 0 auto;
                padding: 20px;
                position: relative;
                background: #FFFFFF;
                box-shadow: 0px 4px 10px 2px rgba(129, 129, 129, 0.25);
                border-radius: 15px;
                min-width: 200px;
            }
            .arbitration-agreement-modal-header{
              display: flex;
              justify-content: end;
            }
            .additional-agreement-modal-header{
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            .arbitration-agreement-modal-header>h3,
            .additional-agreement-modal-header>h3 {
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 20px;
                line-height: 120%;
                color: #2E2E2E;
            }
            .arbitration-agreement-modal-input-div,
            .additional-agreement-modal-input-div{
                margin-bottom: 20px;
            }
            .arbitration-agreement-modal-input-div>input,
            .additional-agreement-modal-input-div>input {
                -webkit-appearance:checkbox;
                color: #0997FF;
                border: 1px solid #0997FF;
                border-radius: 2px;
            }
            .arbitration-agreement-modal-input-div>span,
            .additional-agreement-modal-input-div>span{
                font-family: 'Circe';
                font-style: normal;
                font-weight: 400;
                font-size: 15px;
                line-height: 120%;
                color: #2E2E2E;
            }
            .arbitration-agreement-modal-header>a,
            .additional-agreement-modal-header>a{
                text-decoration: none !important;
                color: #0997FF;
                font-size: 16px;
            }
            #asp_sms [name="sms_asp"] {
                max-width: 100px;
            }
            .asp-sign-accept{
                background: #0997FF;
                border-radius: 5px !important;
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
                color: #FFFFFF;;
            }
            .sms-asp-send-button:hover,.asp-sign-accept:hover {
                background: #FFFFFF;
                box-shadow: 0px 4px 10px 2px rgba(129, 129, 129, 0.25);
                border-radius: 15px;
                color: #0997FF;
                border: 1px solid #0997FF;
            }
            .arbitration-agreement-modal .error,
            .additional-agreement-modal .error{
                color: red !important;
            }
            .text-error{
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
            }

            #removeCardModal {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 30%;
                min-width: 230px;
                height: 200px;
                background: white;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
            }
            .removeCardModal-close {
                position: absolute;
                top: 10px;
                right: 50px;
                font-size: 20px;
                cursor: pointer;
            }
            #removeCardModal-buttons {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;
                margin-top: 20px;
            }
            .arbitration-agreement-modal-input-div>.error,
            .additional-agreement-modal-input-div>.error{
                display: inline !important;
            }
            .sms-asp-send-button{
                background: #0997FF;
                border-radius: 5px !important;
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
                color: #FFFFFF;;
            }
            .wrapper_sms_code{
                display: flex;
                align-items: flex-end;
                gap: 5px;
                flex-wrap: wrap;
            }
            @media screen and (max-width: 520px) {
                .sms-asp-code-error {
                    margin-top: 15px;
                }
            }

            @media screen and (max-width: 540px) {
                .likezaim_banner {
                    width: 100%;
                }
                .likezaim_banner>img {
                    width: 100%;
                }
            }

            .card-confirm {
                width: 350px;
                padding: 20px;
                background: #ffffff;
                border-radius: 12px;
            }

            .card-confirm .error-block, .card-confirm .request-error-block {
                border-radius: 12px;
                padding: 10px;
                margin-bottom: 20px;
                font-size: 14px;
                text-align: center;
            }

            .card-confirm .error-block {
                border: 1px solid #3EE13E;
                color: #3EE13E;
            }

            .card-confirm .request-error-block {
                border: 1px solid #a43540;
                color: #842029;
            }

            .card-confirm .error-block p {
                margin: 0 !important;
                padding: 0 0 10px 0 !important;
            }

            .card-confirm .card-details-block {
                margin-bottom: 20px;
            }

            .card-confirm .card-details-block label {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
                color: #333333;
            }

            .card-confirm .card-details-block input {
                padding: 10px;
                font-size: 14px;
                border: 1px solid #cccccc;
                border-radius: 8px;
                background-color: #f9f9f9;
                margin-bottom: 15px;
                box-sizing: border-box;
                cursor: not-allowed;
            }

            .card-confirm .card-details-block .expiration-date {
                max-width: 100px !important;
            }

            .card-confirm .card-details-block input:read-only {
                background-color: #f0f0f0;
            }

            .card-confirm .confirm-button {
                padding: 15px 30px;
                font-size: 16px;
                color: white;
                background-color: #00BB00;
                border: none;
                border-radius: 18px;
                cursor: pointer;
                box-shadow: 0 6px 12px rgba(0, 187, 0, 0.4);
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            }

            .card-confirm .confirm-button:hover {
                transform: translateY(-2px); /* Slight lift effect on hover */
                box-shadow: 0 8px 16px rgba(0, 187, 0, 0.6); /* Stronger shadow on hover */
            }
        </style>

        {if $restricted_mode !== 1}
            <div id="asp_sms" style="display:none;" class="sms-asp-modal">
                <div class="additional-agreement-modal">
                    <div class="additional-agreement-modal-header">
                        <h3>Давайте будем общаться чаще</h3>
                        <a style="display: none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>X</small></a>
                    </div>
                    <div class="additional-agreement-modal-input-div">
                        <input type="checkbox" value="1" name="accept_asp_1" required >
                        <span>Принимаю соглашение на <a href="files/asp/Agreement_to_different_Frequency_Interactions.pdf" style="text-decoration: none;font-weight: bolder" target="_blank"><u>иную частоту взаимодействия</u> </a></span>
                    </div>
                    <button class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept('asp_sms')">Принять</button>
                    <div class="wrapper_sms_code" style="display: none;">
                        <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept('asp_sms') || asp_app.send_sms('asp_sms');">Получить код</div>
                        <input type="text" name="sms_asp_1" disabled />
                        <div class="sms-asp-code-error" style="display: none;"></div>
                    </div>
                </div>
            </div>
        {/if}

        <div id="modal_connect" class="mfp-hide modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Карта добавлена</h5>
                </div>

                <div class="modal-body">
                    {if isset($user->order) && $user->order.status|intval === 2}
                        <p>Хотите привязать новую карту к текущей заявке? После привязки заявка будет проверена повторно.</p>
                    {else}
                        <p>Если в настоящий момент у Вас есть заявка на рассмотрении, то для получения займа на новую карту Вам необходимо обратиться на горячую линию
                            <a class="tel" href="tel:{$config->org_phone|replace:' ':''}">{$config->org_phone}</a> или в чат на нашем сайте.</p>
                    {/if}
                </div>

                <div class="modal-footer">
                    <button class="button button-inverse" onclick="$.magnificPopup.close()">Нет</button>

                    {if isset($user->order) && $user->order.status|intval === 2}
                        <button class="button attach-new-card" data-order-id="{$last_order.id}">
                            Привязать к заявке
                        </button>
                    {/if}
                </div>
            </div>
        </div>
        
        {*        <input type="hidden" data-id = "{$user->id}" class="user-id">*}
{*                <div id="asp_sms">*}
{*                    <a style="display: none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>пропустить</small></a>*}
{*                    <h5>Может будем общаться чаще?<br/>*}
{*                        Повысить уровень доверия и привлекательности в компании.</h5>*}
{*                    <label class="big left">*}
{*                        <div class="checkbox">*}
{*                            <input type="checkbox" value="1" name="accept_asp" required />*}
{*                            <span></span>*}
{*                        </div> Я ознакомлен и согласен со <a style="margin-left: 5px;" href="/files/docs/asp_zaim.pdf" target="_blank">следующим</a>*}
{*                    </label>*}
{*                    <div class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept();">Подписать</div>*}
{*                    <div class="wrapper_sms_code" style="display: none;">*}
{*                        <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept() || asp_app.send_sms();">Получить код</div>*}
{*                        <input type="text" name="sms_asp" disabled />*}
{*                        <div class="sms-asp-code-error" style="display: none;"></div>*}
{*                    </div>*}
{*                </div>*}

        <div id="arbitr" style="display:none;" class="arbitration-modal-content sms-asp-modal">
            <div class="arbitration-agreement-modal">
                <div class="arbitration-agreement-modal-header">
                    <a onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void(0)"><small>X</small></a>
                </div>
                <div class="arbitration-agreement-modal-input-div">
                    <input type="checkbox" value="1" name="accept_asp_2" required >
                    <input type="hidden" name="order_id" value="{$last_order.id}">
                    <input type="hidden" name="user_id" value="{$user->id}">
                    <span>Принимаю арбитражное <u><a href="user/docs?action=arbitration_agreement&order_id={$last_order.id}" style="font-weight: bolder" target="_blank"></u>соглашение</a></span>
                </div>
                <button class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept('arbitr')">Принять</button>
                <div class="wrapper_sms_code" style="display: none;">
                    <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept('arbitr') || asp_app.send_sms('arbitr');">Получить код</div>
                    <input type="text" name="sms_asp_2" disabled />
                    <div class="sms-asp-code-error" style="display: none;"></div>
                </div>
            </div>
        </div>


        <script type="text/javascript">
            $(document).ready(function() {
                const $carousel =   $(".carousel-container-banners .owl-carousel")
                $carousel.owlCarousel({
                    items: 1,
                    loop: $carousel.children().length > 1,
                    center: true,
                    smartSpeed: 4000,
                    autoplay: true,
                    autoplayTimeout: 4000,
                    autoplayHoverPause: true,
                    nav: true,
                    onInitialized: function () {
                        $('.owl-dots').removeClass('owl-dots');
                    },
                    responsive: {
                        0:{
                            stagePadding: $carousel.children().length > 1 ? 15 : 0,
                            margin:10
                        },
                        600: {
                            stagePadding: $carousel.children().length > 1 ? 30 : 0,
                            margin: -40,
                        },
                        1000: {
                            stagePadding: $carousel.children().length > 1 ? 60 : 0,
                            margin: -40,
                        }
                    }
                });
            });

            $bannerButton = $('.banner .banner-button');

            if ($bannerButton.length) {
                $bannerButton.on('click', function(e) {
                    var $self = $(this);

                    if ($self.hasClass('get-access')) {
                        $('.full_payment_button[data-order_id="'+$self.attr('data-order_id')+'"]').click();
                    } else {
                        var banner = e.currentTarget.closest('.banner');
                        var copyText = banner.querySelector('.promocode');

                        navigator.clipboard.writeText(copyText.innerText);

                        var thumbsUp = banner.querySelector('.thumbs-up');
                        thumbsUp.classList.remove('d-none');
                        var icon = banner.querySelector('.icon');
                        icon.classList.add('d-none');

                        setTimeout(function() {
                            thumbsUp.classList.add('d-none');
                            icon.classList.remove('d-none');
                        }, 2000);
                    }
                });
            }

            if ($('#full_payment_amount_done').val() === 'true') {
                var orderId = {$userGift->order_id|default:0}
                $.ajax({
                    url: 'ajax/generate_fd_key.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: '{$user->id}',
                        order_id: orderId,
                        full_payment_amount_done: true
                    },
                    beforeSend: function() {
                        $("body").addClass('is_loading');
                    },
                    success: function(response) {
                        $("body").addClass('is_loading');
                        if (response.success) {
                            window.open(response.login_url, '_blank');
                            location.reload();
                        } else {
                            alert(response.message || 'Не удалось сгенерировать ключ');
                        }
                    },
                    error: function() {
                        alert('Ошибка при запросе на сервер');
                    },
                    complete: function() {
                        $("body").removeClass('is_loading');
                    }
                });
            }

            $(document).ready(function () {
                let value = $('#show-modal-asp').val();
                let admin = $('#input-is-admin').val();
                let looker = $('#input-is-looker').val();
                if (value && value != 0 && looker == 0 && admin == 0) {
                    $('#asp_sms').css('display', 'block');
                    $.magnificPopup.open({
                        items: {
                            src: '#asp_sms'
                        },
                        type: 'inline',
                        showCloseBtn: false,
                        modal: true,
                    });
                }

                asp_app.init_mask();
                asp_app.init_skip_button_timer();
                $('.sms-asp-modal a, .sms-asp-modal .button').on('click', function () {
                    asp_app.skip_button_second = 10;
                });
            });

            $('.close-modal').click(function () {
                $('#show-modal-asp').val(0);
            });

            const default_sms_delay_seconds = 30;
            const ASP_SMS_ERROR = 'Вы ввели неверный код.';
            const user_phone = '{$user->phone_mobile}';

            // Обработка модальных окон с АСП подписью
            let asp_app = {
                timer_second: 0,
                asp_timer: null,
                skip_button_second: 30,
                skip_button_timer: null,
                skip_button_elements: $('#asp_sms .close-modal, #arbitr .close-modal'),
                accept_field: null,
                code_field: null,
                order_id: null,

                init_skip_button_timer: function () {
                    this.skip_button_timer = setInterval(function () {
                        if (this.skip_button_second === 0) {
                            this.skip_button_elements.show();
                            clearInterval(this.skip_button_timer);
                        }
                        this.skip_button_second--;
                    }.bind(this), 1000);
                },

                validate_accept: function (modalId) {
                    this.accept_field = $('#' + modalId + ' [name^="accept_asp"]');
                    let accept_val = this.accept_field.is(':checked');
                    let error_msg_field = $('#' + modalId + ' .additional-agreement-modal-input-div>span, #' + modalId + ' .arbitration-agreement-modal-input-div>span');

                    $('#' + modalId + ' .text-error').remove();
                    error_msg_field.removeClass('error');
                    if (accept_val) {
                        $('#' + modalId + ' label').removeClass('error');
                    } else {
                        error_msg_field.addClass('error').after('<p class="text-error">Для продолжения необходимо Ваше согласие</p>');
                    }

                    return !error_msg_field.hasClass('error');
                },

                click_asp_accept: function (modalId) {
                    if (this.validate_accept(modalId)) {
                        $('#' + modalId + ' .asp-sign-accept').hide();
                        $('#' + modalId + ' .wrapper_sms_code').show();
                    }
                },

                // выключение таймера и снятие блокировок
                delete_timer: function (modalId) {
                    clearInterval(this.asp_timer);
                    this.asp_timer = null; // сбрасываем таймер
                    $('#' + modalId + ' .sms-asp-send-button').removeClass('disabled').text('Отправить ещё раз');
                    $('#' + modalId + ' [name^="sms_asp"]').val('').prop('disabled', true);
                    $('#' + modalId + ' .sms-asp-code-error').hide();
                },

                // функция таймера отправки смс
                init_timer: function (modalId, seconds) {
                    this.timer_second = seconds;
                    $('#' + modalId + ' .sms-asp-send-button').addClass('disabled');
                    $('#' + modalId + ' [name^="sms_asp"]').prop('disabled', false);

                    this.asp_timer = setInterval(function () {
                        if (this.timer_second === 0) {
                            this.delete_timer(modalId);
                        } else {
                            $('#' + modalId + ' .sms-asp-send-button').text(this.timer_second);
                        }
                        this.timer_second--;
                    }.bind(this), 1000);
                },

                // отправка СМС
                send_sms: function (modalId) {
                    // Если таймер уже запущен, не запускаем новый
                    if (this.asp_timer) {
                        return;
                    }

                    this.init_timer(modalId, default_sms_delay_seconds);
                    $.ajax({
                        url: 'ajax/sms.php',
                        data: {
                            phone: user_phone,
                            action: 'send',
                            flag: 'АСП',
                        },
                        success: function (resp) {
                            if (resp.error) {
                                if (resp.error === 'sms_time')
                                    this.init_timer(modalId, resp.time_left);
                                else
                                    console.log(resp);
                            } else {
                                if (resp.mode === 'developer') {
                                    $('#' + modalId + ' [name^="sms_asp"]').prop('disabled', false).val(resp.developer_code);
                                    this.validate_sms_code(modalId);
                                } else {
                                    console.log('response: ', resp);
                                }
                            }
                        }.bind(this)
                    });
                },

                // маска ввода для СМС
                init_mask: function () {
                    $('[name^="sms_asp"]').inputmask({
                        mask: "9999",
                        oncomplete: function () {
                            let modalId = $(this).closest('.sms-asp-modal').attr('id');
                            asp_app.validate_sms_code(modalId);
                        }
                    });
                },

                // проверка СМС
                validate_sms_code: function (modalId) {
                    let smsCode = $('#' + modalId + ' [name^="sms_asp"]').val();

                    let data = {
                        phone: user_phone,
                        action: 'asp_sms',
                        code: smsCode,
                    }

                    if (modalId === 'arbitr') {
                        data.action = 'check_arbitration_agreement'
                        data.order_id = $('#' + modalId + ' [name="order_id"]').val()
                    }

                    $.ajax({
                        url: 'ajax/sms.php',
                        data,
                        success: function (resp) {
                            if (resp.validate_sms !== 0) {
                                $.magnificPopup.close();
                            } else {
                                $('#' + modalId + ' .sms-asp-code-error').show().text(resp.soap_fault ? resp.error : ASP_SMS_ERROR);
                            }
                        }
                    });
                }
            }
        </script>
    </div>
    {if !$blocked_adv_sms}
        <div class="footer__blocked_adv_sms blocked_adv_sms">
            <button type="button" class="button btn-sm">
                Отписаться от рекламных смс
            </button>
        </div>
    {/if}
    

</div>

<div id="ajax_prolongation__content"></div>
<script src="design/{$settings->theme}/js/files_data.app.js?v=1.69" type="text/javascript"></script>
<script>

    </script>
    <script src="design/{$settings->theme}/js/add_card.js?v=1.011" type="text/javascript"></script>

{capture_array key="footer_page_scripts"}
{literal}
    <script type="text/javascript">
        $(".blocked_adv_sms button").on('click', function () {
            const result = confirm('Вы уверены, что хотите отписаться от рекламных смс?');
            if (result) {
                $.ajax({
                    url: 'ajax/user.php?action=blocked_adv_sms',
                    success: function () {
                        $("#blocked_adv_sms").remove();
                        window.location.reload();
                    }
                });
            }
        });

        $(".partner-href").on('click', function (event) {
            event.preventDefault();
            let url = $(this).attr('href');
            $.ajax({
                url: 'ajax/user.php?action=add_statistic_partner_href',
                success: function () {
                    window.location = url;
                }
            });
        });

        $('.modal-remove_card').on('click', function(event) {
            event.preventDefault();
            $('.action-remove_card').attr('data-button-card-id',$(this).attr('data-button-card-id'))
            $.magnificPopup.open({
                items: {
                    src: '#removeCardModal',
                    type: 'inline'
                }
            });
        });

        $('.modal-choose_card').on('click', function(event) {
            event.preventDefault();
            $('.action-choose_card').attr('data-button-card-id',$(this).attr('data-button-card-id'))
            $('.action-choose_card').attr('data-button-order-id',$(this).attr('data-button-order-id'))
            $.magnificPopup.open({
                items: {
                    src: '#chooseCardModal',
                    type: 'inline'
                }
            });
        });

        // Удаление карты
        remove_card = function (card_id) {
            $.ajax({
                url: 'ajax/remove_card.php',
                data: { card_id: card_id, },
                method: 'POST',
                success: function (resp) {
                    if (resp.result && resp.result == 'success') {
                        $.magnificPopup.close()
                        document.querySelector('[data-card-id="' + card_id + '"]').remove(); // Удаляем карту
                        $('li[data-card-id-deleting="'+card_id+'"]').remove();
                        $('.card-list-for-order li:first input[type="radio"]').prop('checked', true);
                        if(document.querySelectorAll('[data-card-id]').length < 2) { // Если одна карта, убираем у неё кнопку удаления
                            $('.modal-remove_card').remove();
                        }else{
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                        alert("Карта успешно удалена из ЛК");
                    } else {
                        if (resp.error == 'card_blocked') {
                            alert("Удаление карты заблокировано. В настоящее время она используется для совершения для операций");
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                        if (resp.error == 'first_card_blocked') {
                            alert("Удаление единственной карты невозможно");
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                    }
                }
            });
        };

        // Выбор карты
        choose_card = function (card_id, order_id) {
            $.ajax({
                url: 'ajax/choose_card.php',
                data: { card_id: card_id, order_id: order_id },
                method: 'POST',
                success: function (resp) {
                    if (resp && resp.error) {
                        alert(resp.error ? resp.error : "Не удалось выбрать карту");
                    }

                    location.reload();
                }
            });
        };

        $('.attach-new-card').on('click', function() {
            const cardId = localStorage.getItem('newCardId');
            const orderId = $(this).data('order-id');

            if (!cardId || !orderId) {
                console.error('Не удалось привязать карту: отсутствует cardId или orderId');
                return;
            }

            choose_card(cardId, orderId);
            $.magnificPopup.close();
        });

        $('.action-remove_card').on('click', function(event){
            event.preventDefault();
            let button = $(event.target),
                card_id = button.attr('data-button-card-id');

            disableButtons( $('.action-remove_card') );
            remove_card(card_id, button);
        });

        $('.action-choose_card').on('click', function (event) {
            event.preventDefault();
            const button = $(event.target);
            const card_id = button.attr('data-button-card-id');
            const order_id = button.attr('data-button-order-id');

            disableButtons($('.action-choose_card'));
            choose_card(card_id, order_id);
        });

        function disableButtons( elems ){
            elems.attr('disabled', 'disabled');
            elems.css('cursor', 'not-allowed');
        }

        function enableButtons( elems ){
            elems.removeAttr('disabled');
            elems.css('cursor', 'pointer');
        }

        let nowHour = new Date().getHours();
        let today = new Date().getDay();

        var isOrganic = ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3'].includes(userUtmSource.trim());
        let isBetween8and19 = (nowHour >= 8 && nowHour <= 18);

        let shouldCheck = !isOrganic || (isOrganic && !isBetween8and19) || crmAutoApprove;

        function shouldShowElements(utmSource, hour, day) {
            var isOrganic = ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3'].includes(utmSource.trim());
            let isOutsideRestrictedHours = (hour >= 10 && hour < 17);
            let isWeekday = (day !== 0 && day !== 6);

            return isOrganic && isOutsideRestrictedHours && isWeekday;
        }

        function toggleVisibility(elementId, shouldShow) {
            let element = document.getElementById(elementId);
            if (element) {
                element.style.display = shouldShow ? 'block' : 'none';
            }
        }

        function setCheckboxState(checkboxId, shouldCheck) {
            let checkbox = document.getElementById(checkboxId);
            if (checkbox) {
                if (shouldCheck) {
                    checkbox.setAttribute("checked", "checked");
                } else {
                    checkbox.removeAttribute("checked");
                }
            }
        }

        $(".get_prolongation_modal").on('click', function () {

            $("body").addClass('is_loading');
            let order_id = $(this).data('order_id'),
                number = $(this).data('number'),
                tv_medical_tariff_id = 0,
                user_id = $(this).data('user'),
                counter = $(this),
                $button = counter.find('input[type=hidden]').val();
            let tv_medical_radio = document.querySelector("#tv_medical__wrapper input[name='tv_medical_id']:checked");
            if (tv_medical_radio) {
                tv_medical_tariff_id = $(tv_medical_radio).val();
            }

            $("#ajax_prolongation__content").load('/ajax/loan.php?action=get_prolongation', {
                order_id,
                number,
                user_id,
                tv_medical_tariff_id
            }, function (response, status, xhr) {
                if (status === "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                } else {
                    $("body").removeClass('is_loading');
                    if ($('#collectionPromo').length <= 0) {
                        $(".js-prolongation-open-modal[data-order_id='" + order_id + "']").trigger('click');
                    }
                    initialize();
                    let shouldShow = shouldShowElements(userUtmSource, nowHour, today);
                    console.log('tv_medical_tariff_id:', tv_medical_tariff_id);

                  /*if (shouldShow && isFirstOrder == 1) {
                      toggleVisibility('checkboxBlock', true);
                  } else {
                      toggleVisibility('checkboxBlock', false);
                  }*/

                    toggleVisibility('checkboxBlock', false);

                    if (overdue > 8) {
                        toggleVisibility('checkboxBlock', true);
                        toggleVisibility('vitaMedContainer', true);
                        toggleVisibility('conciergeServiceContainer', false);
                    } else {
                        toggleVisibility('vitaMedContainer', false);
                        toggleVisibility('conciergeServiceContainer', false);
                    }

                    prolongationRefreshAmount($button);
                }
            });
        });
        $(document).ready(initialize);

        if ((new URLSearchParams(window.location.search)).get("is_prolongation") === "1") {
            $(".get_prolongation_modal:first").trigger('click');
        }
    </script>

{/literal}
{/capture_array}

<div>
    <div style="display: none">
        <div id="accepted_first_order_divide"  class="white-popup-modal wrapper_border-green mfp-hide">
            <div>
                <h4>
                    Не забудьте вернуться завтра за второй частью займа!
                </h4>
                <button class="green button" onclick="$.magnificPopup.close()">Хорошо</button>
            </div>
        </div>
    </div>
    <style>
        #accepted_first_order_divide {
            margin: auto;
        }

        #accepted_first_order_divide > div:first-child {
            display: flex;
            flex-flow: column;
            gap: 40px;
            padding: 30px;
            margin: auto;
            text-align: center;
        }

        @media screen and (min-width: 769px) {
            #accepted_first_order_divide > div:first-child {
                gap: 120px;
            }
        }
        .grace-main-div{
            width:500px;
            border-radius:15px;
            display:inline-block !important;
            padding: 20px;
            box-shadow: 0 1px 15px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.24);
            margin: 20px 0;
        }
        .grace-container-div>h1 {
            color: #2E2E2E;
            font-family: Circe;
            font-size: 20px;
            font-style: normal;
            font-weight: 700;
            line-height: 120%;
            margin: 10px 0 22px;
        }
        .grace-container-div>h4{
            color: #2E2E2E;
            font-family: Circe;
            font-size: 15px;
            font-style: normal;
            font-weight: 400;
            line-height: 120%;
            margin-top: 10px;
        }
        .new-price{
            color: #FF7F09;
            font-family: Circe;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 120%;
        }

        .old-price{
            color: #CECECE;
            font-family: Circe;
            font-size: 18px;
            font-style: normal;
            font-weight: 400;
            line-height: 120%;
            text-decoration-line: line-through;
        }
        .pay-grace {
            border-radius: 5px;
            border: 2px solid #0997FF !important;
            background: #0997FF !important;
            padding: 10px 30px;
            margin-top: 30px;
            margin-bottom:10px;
            color: #FFF !important;
            font-size: 15px;
            font-style: normal;
            font-weight: 700;
            line-height: 100%;
        }
        .pay-grace:hover {
            background: #0997FF !important;
        }
        .get-reference{
            border-radius: 5px;
            border: 2px solid #0997FF;
            padding: 10px 30px;
            background: white;
            color: #0997FF;
            font-size: 15px;
            font-style: normal;
            font-weight: 700;
            line-height: 100%;
        }
        .get-reference{
            background: white !important;
        }
        .form-pay {
            margin: 0 !important;
        }
        #blocked_adv_sms {
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</div>

<!-- Акция коллекшна -->
{if $restricted_mode === 1 && $collectionPromo === true}
    <link href="design/{$settings->theme}/css/collectionPromo.css" rel="stylesheet" type="text/css" >

    <div id="collectionPromo">
        <div class="modal_title">
            {$collectionPromoTitle}
            <a style="display:none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>X</small></a>
            <div style="clear: both"></div>
        </div>

        <div class="modal-body">
            <h3 class="no_bold">
                Получите персональную скидку
                {if $user->balance->discount_date}
                    до {date('d.m.Y', strtotime($user->balance->discount_date) - 86400)}
                {/if}
            </h3>
            <br>

            <h3 class="no_bold">{$collectionPromoSubTitle}</h3>
            <h3 class="no_bold old_amount"><del>{$collectionPromoOldAmount} &#8381;</del></h3>
            <h3 class="new_amount">{$collectionPromoNewAmount} &#8381;</h3>
            <br>
            <h3 class="no_bold">{$collectionPromoMessage}</h3>
            <br>
            <button id="collection_promo_pay_button" class="restrict_button" data-user="{$user->id}"
                    data-event="4" type="button">Оплатить
            </button>

            {if $collectionPromoDoc}
                <br><br>
                <a href="{$config->root_url}/files/docs/akvarius/{$collectionPromoDoc}.pdf" target="_blank">Правила акции</a>
            {/if}
        </div>
    </div>

    {foreach $additional_scripts as $script}
        <script src="design/{$settings->theme}/js/promo/additional/{$script}.js"></script>
    {/foreach}
    <script src="design/{$settings->theme}/js/promo/collectionPromo.js"></script>
{/if}
<script src="design/{$settings->theme}/js/sbp.js"></script>