{if $happy_new_year == true}
    {assign var="total_debt" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni - (($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni) * 30 / 100)}
{else}
    {assign var="total_debt" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni}
{/if}
{assign var="has_penalty" value=$total_debt >= 1001}

{function name="log_fields" button_name=""}
    <input type="hidden" name="ostatok_od" value="{$order_data->balance->ostatok_od}" />
    <input type="hidden" name="ostatok_percents" value="{$order_data->balance->ostatok_percents}" />
    <input type="hidden" name="ostatok_peni" value="{$order_data->balance->ostatok_peni}" />
    <input type="hidden" name="penalty" value="{$order_data->balance->penalty}" />
    <input type="hidden" name="total_debt" value="{$total_debt}" />
    <input type="hidden" name="button_name" value="{$button_name}" />
    <input type="hidden" name="half_additional_service_repayment" value="{$order_data->order->half_additional_service_repayment}" />
    <input type="hidden" name="additional_service_repayment" value="{$order_data->order->additional_service_repayment}" />
    <input type="hidden" name="half_additional_service_so_repayment" value="{$order_data->order->half_additional_service_so_repayment}" />
    <input type="hidden" name="additional_service_so_repayment" value="{$order_data->order->additional_service_so_repayment}" />
{/function}

{if $order_data->balance->zaim_number && $order_data->balance->zaim_number!='Ошибка' && $order_data->balance->zaim_number!='Нет открытых договоров'}
    {if $order_data->balance->sale_info=='Договор продан'}
        <div class="about">
            <div>Договор продан</div>
        </div>

        {if !in_array($user->balance->buyer, ['Правовая защита', 'БИКЭШ']) && !in_array($user->order['status'], [1,2,5,6,7,8,9,10,12])}
            {loan_form cards=$cards}
        {/if}

        {if $order_data->balance->buyer == 'БИКЭШ' && !$order_data->balance->is_cession_shown && file_exists("{$config->root_dir}/files/contracts/Cess/{$order_data->balance->zaim_number}.pdf")}
            <div style="display:block;
                    position: fixed;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    padding-top: 60px;
                    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                    ">
                <div class="cdoctor-modal" style="max-width: 90%;">
                    <div class="cdoctor-modal-title">Ваш договор продан</div>
                    <div class="cdoctor-modal-price" style="margin: 0px 0;">
                        <embed src="{$config->root_url}/files/contracts/Cess/{$order_data->balance->zaim_number}.pdf" style="max-width:100%;height:430px;" type="application/pdf">
                    </div>
                    <div class="cdoctor-modal-link">
                        <a class="button medium" href="user?cession=shown">Я ознакомлен. Закрыть</a>
                    </div>
                </div>
            </div>
        {/if}

    {elseif $order_data->balance->zaim_number=='Ошибка. Обратитесь в офис'}
        <div class="about">
            <div>{$order_data->balance->zaim_number}</div>
        </div>

    {else}
        <div class="about">
            <div>Заём
                {if $order_data->order->organization_id == 6}
                    Аквариус
                {else}
                    {$config->org_name}
                {/if}
                {$order_data->balance->zaim_number}
            </div>
            {if $restricted_mode !== 1}
                <a class="button small button-inverse {*view-contract*} " target="_blank" href="user/docs" data-number="{$order_data->balance->zaim_number}">смотреть договор</a>
            {/if}
        </div>
    {/if}
    {*{if $order_data->failed_sbp}
        <div class="sbp_info">
            Ваш платёж находится в обработке. Ожидайте, пожалуйста
        </div>
    {/if}*}
    {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number != 'Ошибка. Обратитесь в офис'}

        {include file='user_loan_balance.tpl' user_balance=$order_data->balance orderData=$order_data}

        {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number!='Нет открытых договоров' && $order_data->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
            {if $loan_expired}
                <div>
{*                    <p class="text-red">Ваш заём просрочен</p>*}
                </div>
            {/if}
        {/if}
        {if $order_data->balance->prolongation_amount == 0}
            <div class="prolongation-notification-main">
                <div class="prolongation-notification">
                    <p>
                        К сожалению Вам сейчас <span class="prolongation-not-available">недоступна пролонгация</span>,
                        но закрывая текущий займ Вам будет доступен новый!
                    </p>
                </div>
                <div class="prolongation-notification-details">
                    <div>
                        <p>Пролонгация договора займа, на основании ст. 308.3, 309, 310, 314 и 811 Гражданского кодекса
                            Российской Федерации, является <b>правом</b>, а не <b>обязанностью</b> микрофинансовой организации.</p>
                        <p>Право требования представляет собой возможность на удовлетворение законного интереса одного
                            лица (Кредитора) другим лицом (Заемщиком) путём выполнения конкретных действий, <b>в данном
                                случае закрытие всей суммы займа</b> с начисленными по день исполнения требования
                            процентами.</p>
                    </div>
                    <button class="btn-close-prolongation-notification">Понятно</button>
                </div>
            </div>
        {elseif ($order_data->balance->prolongation_amount > 0 && $user->balance->prolongation_count <= 5) || $order_data->balance->calc_percents > 0}
            {if $order_data->balance->loan_type != 'IL'}
            <div class="user_payment_form">
                {if !!$smarty.cookies.error}
                    <h5 style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    {$smarty.cookies.error}
                                </h5>
                {/if}
                {if $order_data->balance->last_prolongation == 1}
                    <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    У вас осталась последняя пролонгация
                                </span>
                {/if}
                {if $order_data->balance->last_prolongation == 2}
                    <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    Уважаемый клиент, Вы использовали лимит пролонгаций по данному займу.
                                    <br />
                                    Для формирования позитивной кредитной истории срочно погасите заем!
                                </span>
                {/if}
                {if $order_data->balance->last_prolongation != 2}
                    <div class="action flex-block">

                            <button id="button_{$counter}" class="payment_button green button big get_prolongation_modal js-save-click"
                                    data-order_id="{$order_data->order->order_id}"
                                    data-user="{$user->id}"
                                    data-event="1"
                                    type="button"
                                    data-number="{$order_data->balance->zaim_number}">
                                Минимальный платеж
                                <input type="hidden" id="number_{$counter}" value="{$order_data->balance->zaim_number}">
                                {if $order_data->id|@array_search:[299082, 278878, 246778, 153750]}
                                    <span class="user_amount_pay">{$order_data->balance->ostatok_percents}</span>
                                {else}
                                    <span class="payment_button__amount">
                                        {$order_data->balance->ostatok_percents
                                        + $order_data->balance->ostatok_peni
                                        + $order_data->balance->calc_percents
                                        + ($order_data->order->additional_service_multipolis|intval * $order_data->multipolis_amount)
                                        + ($order_data->order->additional_service_tv_med|intval * $tv_medical_price)}
                                    </span>
                                {/if} &nbsp;руб
                            </button>
                            <button style="display: none!important;" class="js-prolongation-open-modal js-save-click" data-order_id="{$order_data->order->order_id}" data-user="{$user->id}" data-event="1" type="button" data-number="{$order_data->balance->zaim_number}"></button>
                            <div class="min_payment_info">
                                Знаете ли вы, что: при регулярной оплате минимальных платежей в МФО ваша кредитная история становится лучше, рейтинг доверия повышается, а значит кредитный лимит будет максимальным
                            </div>

                    </div>

                {/if}

            </div>
            {/if}
        {/if}
    {else }
        <div class="about">
{*            <div>Открытых займов не найдено!</div>*}
        </div>

        {if $user->file_uploaded || Helpers::isFilesRequired($user)}
            {loan_form cards=$cards}
        {/if}
    {/if}

    {if $order_data->balance->sum_with_grace}
{*        <div class="grace-main-div" id="grace-div">*}
{*            <div class="grace-container-div">*}
{*                <h1>Вам доступна оплата со скидкой для закрытия займа</h1>*}
{*                <h4>Остаток задолженности с учетом скидки <span*}
{*                            class="new-price">{str_replace(',', '', number_format($order_data->balance->sum_od_with_grace + $order_data->balance->sum_percent_with_grace, 2))}</span>*}
{*                    <span class="old-price">{($order_data->balance->ostatok_od+$order_data->balance->ostatok_percents+$order_data->balance->ostatok_peni+$order_data->balance->penalty)}</span>*}
{*                </h4>*}
{*                <form method="POST" action="user/payment" class="user_payment_form form-pay">*}
{*                    <input type="hidden" name="number" value="{{$order_data->balance->zaim_number}}"/>*}
{*                    <input type="hidden" name="grace_payment" value="true"/>*}
{*                    <input type="hidden"  name="order_id" value="{$order_data->order->order_id}"/>*}
{*                    <input type="hidden" class="payment_amount" data-order_id="{$order_data->balance->zaim_number}"*}
{*                           data-user_id="{$order_data->balance->user_id}" name="amount"*}
{*                           value="{str_replace(',', '', number_format($order_data->balance->sum_od_with_grace + $order_data->balance->sum_percent_with_grace, 2))}"*}
{*                           max="{str_replace(',', '', number_format($order_data->balance->sum_od_with_grace + $order_data->balance->sum_percent_with_grace, 2))}"*}
{*                           min="1">*}
{*                    <button class="pay-grace payment_button button button-inverse js-save-click" data-event="5">Оплатить*}
{*                        со скидкой*}
{*                    </button>*}
{*                </form>*}
{*                <button class="get-reference" data-number = "{$balance->zaim_number}" data-user = "{$balance->user_id}" disabled>Получить справку об отсутствии задолженности</button>*}
{*            </div>*}
{*        </div>*}
    {/if}
    {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number && $order_data->balance->zaim_number!='Ошибка' && $order_data->balance->zaim_number!='Нет открытых договоров'}

        {if $order_data->balance->loan_type == 'IL'}

            {include file='installment/payment_buttons.tpl'}

        {else}

        {if $order_data->balance->last_prolongation != 2}

            {if $order_data->balance->prolongation_amount > 0 && $user->balance->prolongation_count <= 5}
                <div class="user_payment_form" style="margin-top:20px;">
                    <div class="action">
                        <button class="payment_button button button-inverse js-save-click" data-user="{$user->id}" data-event="2" onclick="$('#close_credit_form_{$order_data_index}').fadeIn('fast');$(this).hide()"
                                type="button">Погасить заём полностью
                        </button>
                    </div>
                </div>

            {else}
                <form method="POST" action="user/payment" class="user_payment_form">
                    <div class="action">
                        <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
                        <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />

                        {log_fields button_name="full_1"}
                        
                        {if $order_data->order->additional_service_repayment || $order_data->order->half_additional_service_repayment}
                            {if $order_data->order->additional_service_repayment}
                                {assign var="price" value=$vita_med->price}
                            {elseif $order_data->order->half_additional_service_repayment}
                                {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
                            {else}
                                {assign var="price" value=0}
                            {/if}
                            
                            {if $order_data->order->additional_service_so_repayment}
                                {assign var="oracle_price" value=$star_oracle->price}
                            {elseif $order_data->order->half_additional_service_so_repayment}
                                {math equation="floor(oracle_price / 2)" oracle_price=$star_oracle->price assign="oracle_price"}
                            {else}
                                {assign var="oracle_price" value=0}
                            {/if}

                            {if $has_penalty}
                                <input type="hidden" name="tv_medical_amount" value="{$price}"/>
                                <input type="hidden" name="tv_medical" value="1"/>
                                <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                <input type="hidden" name="star_oracle_amount" value="{$oracle_price}"/>
                                <input type="hidden" name="star_oracle" value="1"/>
                                <input type="hidden" name="star_oracle_id" value="{$star_oracle->id}"/>
                                {assign var="amount_value" value=$total_debt + $price + $oracle_price + $order_data->balance->penalty}
                            {else}
                                {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                            {/if}
                        {else}
                            {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                        {/if}


                        <input style="display:none" class="payment_amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount"
                               value="{$amount_value}"
                               max="{$amount_value}" min="1" />
                        <button class="payment_button button button-inverse js-save-click pay-full" data-user="{$user->id}" data-event="5" type="submit">Погасить заём полностью</button>
                    </div>
                </form>

            {/if}
        {/if}

        {if $order_data->refinance}
            {include file='refinance.tpl'}
        {/if}

        <div id="close_credit_form_{$order_data_index}"  style="margin-top:15px;{if $order_data->balance->last_prolongation != 2}display:none{/if}">
            {if $order_data->balance->last_prolongation != 2 && $user->balance->prolongation_count <= 5}
                <div style="max-width:500px;margin-bottom:10px;">
                    <p style="color:#080;margin-bottom:10px;">
                        При оплате минимальной суммы ваша кредитная история станет лучше, а кредитный лимит максимальным
                    </p>
                    <button class="payment_button green button big js-prolongation-open-modal js-save-click" data-user="{$user->id}" data-event="3" type="button" data-number="{$order_data->balance->zaim_number}">
                        Минимальный платеж
                        {if $order_data->id|@array_search:[299082, 278878, 246778, 153750]}
                            <span class="user_amount_pay">{$order_data->balance->ostatok_percents}</span>
                        {else}
                            <span class="payment_button__amount">
                               {$order_data->balance->ostatok_percents
                               + $order_data->balance->ostatok_peni
                               + $order_data->balance->calc_percents
                               + ($order_data->order->additional_service_multipolis|intval * $order_data->multipolis_amount)
                               + ($order_data->order->additional_service_tv_med|intval * $tv_medical_price)}
                            </span>
                        {/if} &nbsp;руб

                    </button>

                </div>
            {/if}
            <form method="POST" action="user/payment" class="user_payment_form">
                <div class="action">
                    <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                    <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>

                    {log_fields button_name="full_2"}
                    
                    {if $order_data->order->additional_service_repayment || $order_data->order->half_additional_service_repayment}
                        {if $order_data->order->additional_service_repayment}
                            {assign var="price" value=$vita_med->price}
                        {elseif $order_data->order->half_additional_service_repayment}
                            {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
                        {else}
                            {assign var="price" value=0}
                        {/if}

                        {if $order_data->order->additional_service_so_repayment}
                            {assign var="oracle_price" value=$star_oracle->price}
                        {elseif $order_data->order->half_additional_service_so_repayment}
                            {math equation="floor(oracle_price / 2)" oracle_price=$star_oracle->price assign="oracle_price"}
                        {else}
                            {assign var="oracle_price" value=0}
                        {/if}

                        {if $has_penalty}
                            <input type="hidden" name="tv_medical_amount" value="{$price}"/>
                            <input type="hidden" name="tv_medical" value="1"/>
                            <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                            <input type="hidden" name="star_oracle_amount" value="{$oracle_price}"/>
                            <input type="hidden" name="star_oracle" value="1"/>
                            <input type="hidden" name="star_oracle_id" value="{$star_oracle->id}"/>
                            {assign var="amount_value" value=$total_debt + $price + $oracle_price + $order_data->balance->penalty}
                        {else}
                            {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                        {/if}
                    {else}
                        {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                    {/if}

                    <input style="display:none" class="payment_amount"
                           data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                           name="amount"
                           value="{$amount_value}"
                           max="{$amount_value}" min="1"/>
                    <button data-order_id="{$order_data->balance->zaim_number}" class="payment_button button button-inverse js-save-click full_payment_button pay-full" data-user="{$user->id}"
                            data-event="5" type="submit">Погасить заём полностью
                    </button>
                </div>
            </form>
        </div>

        {if $restricted_mode !== 1}
            <form method="POST" action="user/payment" class="user_payment_form">
                <div class="action">
                    <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                    <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>

                    {log_fields button_name="full_3"}
                    
                    {if $order_data->order->additional_service_repayment || $order_data->order->half_additional_service_repayment}
                        {if $order_data->order->additional_service_repayment}
                            {assign var="price" value=$vita_med->price}
                        {elseif $order_data->order->half_additional_service_repayment}
                            {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
                        {else}
                            {assign var="price" value=0}
                        {/if}

                        {if $order_data->order->additional_service_so_repayment}
                            {assign var="oracle_price" value=$star_oracle->price}
                        {elseif $order_data->order->half_additional_service_so_repayment}
                            {math equation="floor(oracle_price / 2)" oracle_price=$star_oracle->price assign="oracle_price"}
                        {else}
                            {assign var="oracle_price" value=0}
                        {/if}

                        {if $has_penalty}
                            <input type="hidden" name="tv_medical_amount" value="{$price}"/>
                            <input type="hidden" name="tv_medical" value="1"/>
                            <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                            <input type="hidden" name="star_oracle_amount" value="{$oracle_price}"/>
                            <input type="hidden" name="star_oracle" value="1"/>
                            <input type="hidden" name="star_oracle_id" value="{$star_oracle->id}"/>
                            {assign var="amount_value" value=$total_debt + $price + $oracle_price + $order_data->balance->penalty}
                        {else}
                            {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                        {/if}
                    {else}
                        {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                    {/if}


                    <input style="display:none" class="payment_amount"
                           data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                           name="amount"
                           value="{$amount_value}"
                           max="{$amount_value}" min="1"/>
                    <button class="payment_button button button-inverse js-save-click" data-user="{$user->id}"
                            data-event="4" type="submit">Погасить заём полностью и взять новый
                    </button>
                </div>
            </form>
        {/if}


        <div class="user_payment_form">
            <div class="action">
                <button class="payment_button button button-inverse js-save-click" data-user="{$user->id}" data-event="6" onclick="$('#other_summ_{$order_data_index}').fadeIn('fast');$(this).hide()" type="button">Оплатить другую сумму</button>
            </div>
        </div>
            <form method="POST" action="user/payment" id="other_summ_{$order_data_index}"
                  class="user_payment_form user_payment_form_other" style="display:none">
                <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>

                {log_fields button_name="partial"}
                
                <div class="action">
                    {if $order_data->balance->prolongation_amount > 0}
                        <div style="max-width:500px;">
                            <p style="margin-bottom:0;">Внимание, после оплаты дата возврата займа не изменится!
                                <br/>Во избежание возникновения просрочки и ухудшения вашей кредитной истории,
                                пожалуйста, убедитесь в том, что вы успеете полностью погасить заём
                                до {$order_data->balance->payment_date|date}.
                                <br/>Если вы хотите пролонгировать заём, воспользуйтесь кнопкой «Минимальный платеж»
                            </p>
                        </div>
                    {/if}
                    <p style="margin-bottom:0;">Другая сумма</p>

                    {if $total_debt >= 1001}
                        {if $order_data->order->additional_service_partial_repayment || $order_data->order->half_additional_service_partial_repayment}
                            <input type="hidden" name="tv_medical_amount" value="0"/>
                            <input type="hidden" name="tv_medical" value="0"/>
                            <input type="hidden" name="tv_medical_id" value="0"/>
                        {/if}

                        {if $order_data->order->additional_service_so_partial_repayment || $order_data->order->half_additional_service_so_partial_repayment}
                            <input type="hidden" name="star_oracle_amount" value="0"/>
                            <input type="hidden" name="star_oracle" value="0"/>
                            <input type="hidden" name="star_oracle_id" value="0"/>
                        {/if}
                    {/if}

                    <input class="payment_amount" data-order_id="{$order_data->balance->zaim_number}"
                           data-user_id="{$user->id}" type="text" name="common_amount" id="common_amount_{$order_data_index}" value="" min="1" max="{$total_debt}"/>
                    <input class="hidden_amount" type="hidden" name="amount" id="hidden_amount_{$order_data_index}" value=""/>
                    <button class="payment_button button medium js-save-click" data-user="{$user->id}" data-event="7"
                            type="submit">Оплатить
                    </button>
                </div>
            </form>
        {/if}
    {/if}
{elseif !$order_data->order && $order_data->balance->zaim_number == 'Нет открытых договоров'}
    <div class="about">
{*        <div>Открытых займов не найдено!</div>*}
    </div>

    {if $user->file_uploaded || Helpers::isFilesRequired($user)}
        {loan_form cards=$cards}
    {/if}
{/if}

{if ($divide_pre_order_is_new && $order_data_index == 0) || ($order_data_index == 1 && in_array($divide_order->data->status, ['APPROVED', 'ISSUED']) && !in_array($order_data->order->status_1c, ['5.Выдан', '6.Закрыт']))}{*если новый разделенный займ одобрен*}
    {include 'divide_order.tpl'}
{/if}

{assign var="tv_medical_tariffs" value=$tv_medical_tariffs|json_encode}
{assign var="star_oracle_tariffs" value=$star_oracle_tariffs|json_encode}

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {

      const orderID= "other_summ_"+{$order_data_index|escape:'javascript'}
      
      if (!orderID) {
        return;
      }
      const paymentForm= document.getElementById(orderID)
        

            const orderDataIndex = paymentForm.id.replace('other_summ_', '');

            const amountInput = document.getElementById('common_amount_' + orderDataIndex);
            const hiddenAmountInput = document.getElementById('hidden_amount_' + orderDataIndex);
            const tvMedicalPriceInput = paymentForm.querySelector('input[name="tv_medical_amount"]');
            const tvMedicalValue = paymentForm.querySelector('input[name="tv_medical"]');
            const tvMedicalIdInput = paymentForm.querySelector('input[name="tv_medical_id"]');

          const starOraclePriceInput = paymentForm.querySelector('input[name="star_oracle_amount"]')
          const starOracleIdInput = paymentForm.querySelector('input[name="star_oracle_id"]')
          const starOracleValue = paymentForm.querySelector('input[name="star_oracle"]')

            if (!amountInput || !hiddenAmountInput) {
                return;
            }

            const totalDebt = parseFloat("{$total_debt|escape:'javascript'}");
            const additionalServicePartialRepayment = "{$order_data->order->additional_service_partial_repayment|escape:'javascript'}";
            const additionalServiceHalfPartialRepayment = "{$order_data->order->half_additional_service_partial_repayment|escape:'javascript'}"

          const additionalServiceSOPartialRepayment = "{$order_data->order->additional_service_so_partial_repayment|escape:'javascript'}"
          const halfAdditionalServiceSOPartialRepayment = "{$order_data->order->half_additional_service_so_partial_repayment|escape:'javascript'}"

            function calculateTvMedicalPrice(enteredAmount) {
                const tariffs = JSON.parse('{$tv_medical_tariffs|escape:"javascript"}').map(tariff => {
                    return {
                        min: +tariff.from_amount,
                        max: +tariff.to_amount,
                        price: +tariff.price,
                        id: +tariff.id,
                        value: +tariff.is_new
                    };
                });

                const foundTariff = tariffs.find(tariff => enteredAmount >= tariff.min && enteredAmount <= tariff.max);
                return foundTariff ?? { price: 0, id: 0, value: 0 };
            }

          function calculateStarOraclePrice (enteredAmount) {
            const tariffs = JSON.parse('{$star_oracle_tariffs|escape:"javascript"}').map(tariff => {
              return {
                min: +tariff.from_amount,
                max: +tariff.to_amount,
                price: +tariff.price,
                id: +tariff.id,
                value: +tariff.is_new
              }
            })

            const foundTariff = tariffs.find(tariff => enteredAmount >= tariff.min && enteredAmount <= tariff.max)
            return foundTariff ?? { price: 0, id: 0, value: 0 }
          }

            function updateHiddenAmount() {
                let enteredAmount = parseFloat(amountInput.value);

              let oraclePrice = 0
              let tvMedPrice = 0

                if (enteredAmount > totalDebt) {
                    enteredAmount = totalDebt;
                    amountInput.value = totalDebt;
                }

              if (totalDebt >= 1001) {

                if (additionalServicePartialRepayment == 1 || additionalServiceHalfPartialRepayment == 1) {
                    const { price: originalPrice, id, value } = calculateTvMedicalPrice(enteredAmount)
                    const price = additionalServicePartialRepayment == 1 ? originalPrice : originalPrice / 2
                  tvMedPrice = price
                    tvMedicalPriceInput.value = price
                    tvMedicalIdInput.value = id
                    tvMedicalValue.value = value
                }

                if (additionalServiceSOPartialRepayment == 1 || halfAdditionalServiceSOPartialRepayment == 1) {
                  const { price: originalPrice, id, value } = calculateStarOraclePrice(enteredAmount)
                  const price = additionalServiceSOPartialRepayment == 1 ? originalPrice : originalPrice / 2
                  oraclePrice = price
                  starOraclePriceInput.value = price
                  starOracleIdInput.value = id
                  starOracleValue.value = value
                }

                hiddenAmountInput.value = enteredAmount + oraclePrice + tvMedPrice

              } else {
                hiddenAmountInput.value = enteredAmount
                if (tvMedicalPriceInput) {
                  tvMedicalPriceInput.value = ''
                  tvMedicalIdInput.value = ''
                  tvMedicalValue.value = ''
                }
                if (starOraclePriceInput) {
                  starOraclePriceInput.value = ''
                  starOracleIdInput.value = ''
                  starOracleValue.value = ''
                }
              }
            }

            amountInput.addEventListener('input', function() {
                let value = this.value;
                value = value.replace(/[^0-9]/g, '');

                if (value.startsWith('0')) {
                    value = value.substring(1);
                }

                if (parseFloat(value) > totalDebt) {
                    value = totalDebt.toString();
                }

                this.value = value;
                updateHiddenAmount();

                if (this.value) {
                    amountInput.style.border = '';
                    amountInput.style.borderRadius = '';
                }
            });

            paymentForm.addEventListener('submit', function(event) {
                if (!amountInput.value) {
                    event.preventDefault();
                    amountInput.style.border = '2px solid #ff0500';
                    amountInput.style.borderRadius = '5px';
                } else {
                    paymentForm.submit();
                }
            });
        });

</script>
