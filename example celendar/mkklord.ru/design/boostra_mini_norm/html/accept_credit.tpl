<div id="accept_block_{$user_order['id']}" data-order="{$user_order['id']}" class="{if $user_order['utm_source'] == 'cross_order'}cross_order_accept{/if}">

    <p class="accept_message">
        {if $notOverdueLoan}
            <h2>
                Отлично! Вы закрыли займ без просрочек и можете оформить новый на льготных условиях.
            </h2>
        {/if}

            {if $user_order['utm_source'] == 'cross_order'}
                {if !$isAutoAcceptCrossOrders}
                Вам дополнительно одобрено {$user_order['approve_max_amount']} руб <br />
                {/if}
                <button
                        type="button" class="
                            button big {if $config->snow}snow-relative primary{else}orange{/if}
                            {if $user_order['noactive'] && !$isAutoAcceptCrossOrders}noactive js-noactive{/if}
                            " id="open_accept_modal"
                        style="{if $isAutoAcceptCrossOrders}display:none!important;{/if}"
                >
                    {if $config->snow}
                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                    {/if}
                    Получить ещё {$user_order['approve_max_amount']} руб
                </button>

            {else}
                Поздравляем! По вашей заявке одобрено <span id="approve_max_amount">
                {if $divide_pre_order}
                    {$divide_pre_order->amount + $user_order['amount']}
                {else}
                    {if $isAutoAcceptCrossOrders}
                        {$totalApproveAmount}
                    {else}
                        {$user_order['approve_max_amount']}
                    {/if}
                {/if}
            </span> руб. {if $autoapprove_other_org}на карту<br><span style="color: #FF0000">{$last_order_card->pan}</span>{/if}
            <br />
            {if 0 && $user_discount}
            Вы можете принять решение до {$user_discount->end_date|date}.
            {else}
            Вы можете принять решение до {$user_order['approved_period']}.
            {/if}
            <br />
            {if !$divide_pre_order}
                <button type="button" class="get_money_btn button big {if $config->snow}snow-relative primary{else}green{/if}"
                    id="{if $autoapprove_card_reassign}autoapprove_card_reassign{elseif $autoapprove_wrong_card}autoapprove_card_modal_btn{else}open_accept_modal{/if}">
                    {if $config->snow}
                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                    {/if}
                    Получить деньги
                </button>
            {else}
                {include 'divide_order.tpl'}
            {/if}
        {/if}
    
        {if !$user_order['noactive']}
            {if ($user_order['approve_max_amount'] > $user_order['user_amount']) && ($user_order['approve_max_amount'] != 1000 && $user_order['have_close_credits'] == 1 || ($user_order['approve_max_amount'] > $user->first_loan_amount && !$divide_pre_order))}
                {if $user_order['max_period'] > 0 && $user_order['max_amount'] > 30000}
                    {include file='installment/edit_amount.tpl'}
                {else}
                <div id="edit-amount">
                    <h4 class="text-orange">Вы можете изменить сумму займа</h4>
                    <div class="slider-box">
                        <div class="money-edit">
                            <span class="edit-amount-value">
                                {if $user_order['user_amount'] > 0}{$user_order['user_amount']}
                                {else}{$user_order['approve_max_amount'] - 1000}{/if}
                            </span>
                            <span class="ion-btn ion-minus"></span>
                            <div>
                                {*  !!!
                                    Если меняете логику калькулятора - поменяйте и её проверку в UserView (edit_amount action)
                                 *}
                                <input type="text"
                                       id="money-edit"
                                       name="amount_edit"
                                       data-max="{$user_order['approve_max_amount']}"
                                       data-min="{if $user_order['user_amount'] > 0}{$user_order['user_amount']}{else}{$user_order['approve_max_amount'] - 1000}{/if}"
                                       data-step="1000"
                                       data-init_value="{$user_order['amount']}"
                                       value="{$user_order['amount']}" />
                            </div>
                            <span class="ion-btn ion-plus"></span>
                            <span class="edit-amount-value">{$user_order['approve_max_amount']}</span>
                        </div>
                        <div
                            id="full-loan-info"
                            data-percent="{$user_order['percent']}"
                            data-period="{$user_order['period']}"
                            data-promocode="{$user_order['promocode']}"
                            style="font-size: 1.3rem!important;"
                        ></div>
                        <button
                            type="button"
                            class="button bg-orange"
                            id="accept_edit_amount"
                            data-order="{$user_order['id']}"
                            style="display: none; margin-top: 10px;"
                        >Подтвердить изменение</button>
                    </div>
                    {literal}
                        <style>
                            #edit-amount {
                                max-width: 720px;
                            }
                            #edit-amount .money-edit {
                                display: grid;
                                grid-template: 1fr / auto auto 1fr auto auto;
                                align-items: center;
                                grid-gap: 15px;
                            }
                            @media screen and (max-width: 992px) {
    
                            }
                        </style>
                    {/literal}
        {capture_array key="footer_page_scripts"}
                        <script>
                            {literal}
                            function updateFullLoanInfo(loan_sum) {
                                var info_field  = document.querySelector('#full-loan-info');
                                var finish_date = (new Date) * 1 + 86400 * 1000 * info_field.dataset.period;
                                var loan_str = new Intl
                                                    .NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 })
                                                    .format(loan_sum * 1 + loan_sum * info_field.dataset.period * info_field.dataset.percent / 100);
                                var message = `К возврату ${loan_str} до ${(new Date(finish_date)).toLocaleDateString()}`;
    
                                info_field.innerText = message;
                            }
    
                            function changeSliderStyles() {
                                var info_field  = document.querySelector('#full-loan-info');
                                var styles_box  = document.querySelector('#promo-slider-styles');
                                if(info_field.dataset.promocode && !styles_box) {
                                    styles_box = document.createElement('style');
                                    styles_box.id = 'promo-slider-styles';
                                    styles_box.innerHTML = '.irs-slider.single { background-color: #1DD71D!important }'
                                                            + ' .irs-single { color: #1DD71D!important }'
                                                            + ' #full-loan-info { color: #1DD71D!important }';
                                    document.querySelector('head').appendChild(styles_box);
                                }
                            }
                            {/literal}
    
                            updateFullLoanInfo(document.querySelector('#money-edit').value)
                            changeSliderStyles();
    
                            var loanSlider = $("#money-edit").ionRangeSlider({
                                type: "single",
                                postfix: " <span>Р</span>",
                                hide_min_max: true,
                                onChange: function (data) {
                                    if (parseInt(data.from) === parseInt($(data.input).data('init_value'))) {
                                        $("#accept_edit_amount").hide();
                                    } else {
                                        $("#accept_edit_amount").show();
                                    }
                                    updateFullLoanInfo(data.from);
                                },
                                onUpdate: function (data) {
                                    if (parseInt(data.from) === parseInt($(data.input).data('init_value'))) {
                                        $("#accept_edit_amount").hide();
                                    } else {
                                        $("#accept_edit_amount").show();
                                    }
                                    updateFullLoanInfo(data.from);
                                }
                            });
    
                            $("#accept_edit_amount").on('click', function () {
                                let edit_amount = parseInt($("#money-edit").val());
                                let order_id = $(this).data('order');
                                $("body").addClass('is_loading');
    
                                $.post('/user?action=edit_amount', {
                                    'edit_amount': edit_amount, 
                                    'order_id': order_id
                                }).done(function(json) {
                                    if (json.result) {
                                        location.reload();
                                    }
                                });
                            });
    
                        </script>
                        {*Отправка метрики по кнопке получить займ в ЛК в зависимости от типа клиента https://trello.com/c/oL2cPB2c*}
                        <script>
                            $('#open_accept_modal, .get_money_btn').click(function(){
                                {if $user->loan_history|count == 0}
                                    sendMetric('reachGoal', 'get_money_btn_nk');
                                {else}
                                    sendMetric('reachGoal', 'get_money_btn_pk');
                                {/if}
                            });
    
                            $('#autoapprove_card_reassign').click(function (){
                                $(".cards").get(0).scrollIntoView( { behavior: 'smooth' } );
                            });
    
                            $('#autoapprove_card_modal_btn').click(function () {
                                $('#autoapprove_card_modal').show();
                                $.magnificPopup.open({
                                    items: {
                                        src: '#autoapprove_card_modal'
                                    },
                                    type: 'inline',
                                    showCloseBtn: false,
                                    modal: true,
                                });
                            });
    
                            $('#js-other-card-btn').click(function () {
                                $.ajax({
                                    url: 'ajax/autoapprove_actions.php',
                                    data: {
                                        'action': 'reject'
                                    },
                                    success: function(resp){
                                        console.log(resp);
                                        location.reload();
                                    }
                                });
                            });
                        </script>
        {/capture_array}
                </div>
                {/if}
            {/if}
        {/if}
    </p>

    {if !$autoapprove_card_reassign && !$autoapprove_wrong_card}
        <div id="accept_credit" class="accept_credit" style="display:none">
            {if !$settings->enable_loan_nk && !$user_order['have_close_credits']}
            <p class="text-red">
                Произошла техническая ошибка.<br />Попробуйте повторить через час.
            </p>
            {else}
            <form id="accept_credit_form" class="accept_credit_form" onsubmit="ym(45594498,'reachGoal','click_cash'); return true;">

                {foreach $cards as $card}
                    {if $card->id == $user_order['card_id']}
                    <input type="hidden" name="rebill_id" value="{$card->rebill_id}" />
                    {/if}
                {/foreach}

                <input type="hidden" name="order_id" value="{$user_order['id']}" />
                <input type="hidden" name="card_id" value="{$user_order['card_id']}" />
                <input type="hidden" name="uid" value="{$user->uid}" />
                <input type="hidden" name="number" value="{$user_order['1c_id']}" />
                <input type="hidden" name="insurer" value="{$insurer}" />
                <input type="hidden" name="insure" value="{$insure}" />
                <input type="hidden" name="new_nk_flow_path" id="new_nk_flow_path" value="0" />
                <input type="hidden" name="service_recurent" value="1" />
                <input type="hidden" value="1" name="agree_claim_value" id="agree_claim_value">
                <input type="hidden" value="0" name="is_user_credit_doctor" />
                <input type="hidden" value="0" name="is_user_fin_doctor" />


                {if $returnSafetyFlowCreditDoctor}
                    <input type="hidden" value="0" name="is_user_credit_doctor" id="credit_doctor_hidden{$user_order['id']}"/>
                {else}
                    <input type="hidden" value="1" name="is_user_credit_doctor" id="credit_doctor_hidden{$user_order['id']}"/>
                {/if}

                {if $returnSafetyFlowStarOracle}
                    <input type="hidden" value="0" name="is_star_oracle" id="star_oracle_hidden{$user_order['id']}"/>
                {else}
                    <input type="hidden" value="1" name="is_star_oracle" id="star_oracle_hidden{$user_order['id']}"/>
                {/if}


            <h2>К получению <span id="amountToCard">{$user_order['amount']}</span> руб.</h2>
            <p>
                Подписать с помощью смс кода
                <br />
            </p>

            <div class="accept_credit_actions">
                <div>
                    <input type="text" name="sms_code" id="sms_code" value="" placeholder="Код из СМС" />
                    <div class="sms-code-error"></div>
                    <a href="javascript:void(0);" id="repeat_sms" class="repeat_sms" data-phone="{$user->phone_mobile}">отправить код еще раз</a>
                </div>
                <div>
                    <button id="telegram_banner_button_click" class="get_money_btn button medium {if $config->snow}snow-relative primary{else}green{/if}" type="submit">
                        {if $config->snow}
                            <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                        {/if}
                        Получить деньги
                    </button>
                </div>

            </div>

            <div id="not_checked_info" style="display:none">
                <strong style="color:#f11">Вы должны согласиться с договором и нажать "Получить деньги"</strong>
            </div>
            {include file="accept_credit/docs_list_main.tpl"}
        </form>
        {/if}
    </div>
    {/if}


    <div style="display: none">
        <div id="accepted_first_order_divide" class="wrapper_border-green white-popup-modal wrapper_border-green mfp-hide">
            <div>
                <h4>
                    Не забудьте вернуться завтра за второй частью займа!
                </h4>
                <button class="green button" onclick="$.magnificPopup.close()">Хорошо</button>
            </div>
        </div>
    </div>

    <div id="autoapprove_card_modal" class="modal" style="display: none">
        <div class="modal-content autoapprove_card_modal">
            <div>
                <p>Для получения одобренного займа необходимо привязать карту <span style="color: #FF0000">{$last_order_card->pan}</span></p>
            </div>
            <div class="autoapprove_card_modal__buttons">
                <button class="button big green" id="js-assign-old-card-btn">Привязать</button>
                <button class="button big" id="js-other-card-btn">Хочу использовать другую карту</button>
            </div>
            <div>
                <p style="color: #FF0000">При использовании другой карты одобренная заявка аннулируется. Для получения займа необходимо будет подать новую заявку</p>
            </div>
        </div>
    </div>

</div>
<script>
    var isOrganic = "{$isOrganic|escape:'javascript'}"
    // $('#telegram_banner_button_click').click(function() {
    //     window.open($('.telegram_banner a').attr('href'), '_blank')
    // });
</script>


<script type="text/javascript">

  $(function () {
        var _auto = {if $isAutoAcceptCrossOrders && $user_order['utm_source'] != 'cross_order'}1{else}0{/if};
        var $block =  $('#accept_block_{$user_order["id"]}');
        var AcceptCreditApp = new AcceptCredit($block, _auto);
        {if $user_order['utm_source'] != 'cross_order' && $asp_code_already_sent}
            AcceptCreditApp.open_accept_modal()
            $('html, body').animate({ scrollTop: $block.offset().top }, 'slow');
        {/if}
        {if $user_order['utm_source'] == 'crm_auto_approve'}
        $('#test_open_accept_green').click();
        {/if}
    });

  document.getElementById("agreed_6").addEventListener('change', function () {
      const input = document.getElementsByName("is_user_fin_doctor")[0];
      input.value = Number(this.checked);
  });

</script>
