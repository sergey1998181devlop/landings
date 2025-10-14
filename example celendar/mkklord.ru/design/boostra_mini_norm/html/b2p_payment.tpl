{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{literal}
<style>
    [name="card_id"] {
        display:none
    }
    .payment-card-list {
        padding: 0 2rem;
    }

    .modal-header {
        width: 100%;
        display: block;
        text-align: end;
    }
</style>
<script>

function PaymentApp()
{
    var app = this;

    app.payment_id;

    app.sbp_enabled = $('#sbp_enabled').val();

    if (app.sbp_enabled) {
        app.payment_method = 'sbp';
    } else {
        app.payment_method = 'card';
    }

    app.init = function(){

        if (!app.sbp_enabled) {
            $('#payment-block-card-type').show()
            $('.payment_type_card').addClass('active')
        } else {
            $('.payment_type_card').click(function(){
                $('#payment-block-spb-type').hide()
                $('.payment_type_spb').removeClass('active')

                $('#payment-block-card-type').show()
                $('.payment_type_card').addClass('active')

                app.payment_method = 'card';
            });

            $('.payment_type_spb').click(function(){
                $('#payment-block-card-type').hide()
                $('.payment_type_card').removeClass('active')

                $('.payment_type_spb').addClass('active')
                $('#payment-block-spb-type').show()
                app.payment_method = 'sbp';
            });
        }

        $('.cancel_payment').click(function(){
            location.href = 'user';
        });

        $('#confirm_payment').click(function(e){
            app.confirm_payment(e);
        });

        $('#gpay').click(function(e){
            $('[name=card_id] [value=other]').attr('checked', true);
            app.confirm_payment(e);
        });

        $('.exitpool_button').click(function(e){
            e.preventDefault();

            app.send_exitpool();
        })
    };

    app.send_exitpool = function(){
        if ($('[name=payment_exitpool]:checked').length > 0)
        {
            var variant_id = $('[name=payment_exitpool]:checked').val();
            $.ajax({
                type: 'POST',
                url: '/ajax/exitpool.php',
                data: {
                    action: 'payment_exitpool',
                    variant_id: variant_id
                },
                beforeSend: function(){
                    $('.payment-block').addClass('loading');
                },
                success: function(){
                    location.href = 'user';
                }
            })
        }
        else
        {
            alert('Выберите вариант ответа');
        }
    }

    app.confirm_payment = function(e){

        var amount = $('[name=amount]').val();
        var collection_promo = $('[name=collection_promo]').val();

        let multipolis = $('[name="multipolis"]').val(),
            multipolis_amount = $('[name="multipolis_amount"]').val(),
            tv_medical = $('[name="tv_medical"]').val(),
            insure = $('[name=insure]').val(),
            tv_medical_id = $('[name="tv_medical_id"]').val(),
            tv_medical_amount = $('[name="tv_medical_amount"]').val(),
          
            star_oracle = $('[name="star_oracle"]').val(),
            star_oracle_id = $('[name="star_oracle_id"]').val(),
            star_oracle_amount = $('[name="star_oracle_amount"]').val(),
            action_type = $('[name="action_type"]').val(),
            order_id = $('[name="order_id"]').val(),
            organization_id = $('[name="organization_id"]').val(),
            calc_percents = $('[name="calc_percents"]').val();
        var grace_payment = $('[name="grace_payment"]').val();

        var prolongation = $('[name=prolongation]').val();
        var code_sms = $('[name=code_sms]').val();
        var number = $('[name=number]').val();
        var user_id = $('[name=user_id]').val();
        var chdp = $('[name=chdp]').val();
        var pdp = $('[name=pdp]').val();
        var refinance = $('[name=refinance]').val();
        var from = $('[name=from]').val();

        if (amount > 0)
        {
            if ($('[name=card_id]:checked').length > 0)
            {
                $('.payment-block-title').removeClass('error');
                $('.payment-block').addClass('loading');

                var $btn = $('#confirm_payment')
                var $gbtn = $('#gpay')

                var card_id = app.payment_method == 'sbp' ? 0 : $('[name=card_id]:checked').val();

                $.ajax({
                    url: 'ajax/b2p_payment.php',
                    async: false,
                    data: {
                        action: 'get_payment_link',
                        amount: amount,
                        prolongation: prolongation,
                        code_sms: code_sms,
                        web: 1,
                        order_id,
                        organization_id,
                        insure,
                        multipolis,
                        multipolis_amount,
                        card_id: card_id,
                        number: number,
                        user_id: user_id,
                        tv_medical,
                        tv_medical_id,
                        tv_medical_amount,
                        star_oracle,
                        star_oracle_id,
                        star_oracle_amount,
                        action_type,
                        calc_percents,
                        grace_payment,
                        chdp,
                        pdp,
                        from, 
                        refinance,
                        payment_method: app.payment_method,
                        collection_promo: collection_promo
                    },
                    success: function(resp){

                        if (!!resp.error)
                        {
                            $('.payment-block').removeClass('loading').addClass('error');
                            $('.payment-block-error p').html('Ошибка: '+resp.error);
                            e.preventDefault();
                            return false;
                        }
                        else
                        {
                            if (resp?.payment_link && $('#click_event').val() == 1) {
                                window.location.href = resp.payment_link;
                            }

                            app.payment_id = resp.payment_id;
                            app.check_state(app.payment_id);
//                            document.cookie = "go_payment=1; path=/;";

                            $btn.attr('href', resp.payment_link);
                            $gbtn.attr('href', resp.payment_link);



                            return true;
                        }

                    }
                })
            }
            else
            {
                $('.payment-block-title').addClass('error');
            }
        }
        else
        {
            $('.payment-block').removeClass('loading').addClass('error');
            $('.payment-block-error p').html('Сумма должна быть больше нуля.');

            e.preventDefault();
            return false;
        }
    };


    app.check_state = function(payment_id){
        app.check_timeout = setTimeout(function(){
            $.ajax({
                url: 'ajax/b2p_payment.php',
                data: {
                    action: 'get_state',
                    payment_id: app.payment_id,
                },
                success: function(resp){
console.log(resp)
                    if (!!resp.error)
                    {
                        $('.payment-block').removeClass('loading').addClass('error');
                        $('.payment-block-error p').html('Ошибка: '+resp.error);

                    }
                    else
                    {
                        if (resp.Status == 'CONFIRMED')
                        {
                            if ($('.payment-block-exitpool').length > 0)
                            {
                                $('.payment-block').removeClass('loading').addClass('exitpool');
                            }
                            else
                            {
                                $('.payment-block').removeClass('loading').addClass('success');
                                $('.js-payment-block-success p').html('Спасибо, оплата принята.');
                            }
                        }
                        else if (resp.Status == 'REJECTED')
                        {
                            $('.payment-block').removeClass('loading').addClass('error');
                            $('.payment-block-error p').html('Не получилось оплатить<br />'+resp.Message);
                        }
                        else
                        {
                            app.check_state();

                        }
                    }
                }
            })
        }, 5000);
    }

    ;(function(){
        app.init();
    })();
};
$(function(){
    new PaymentApp();
})




</script>

{/literal}

<section id="private">
	<div>
		<div class="page-title">Подтверждение платежа</div>
        <div class="payment-block">

            <input id="sbp_enabled" type="hidden" name="sbp_enabled" value="{$sbp_enabled}" />
            {if $discount > 0}
                <input type="hidden" id="collection_promo" name="collection_promo" value="{$discount}" />
            {else}
                <input type="hidden" id="collection_promo" name="collection_promo" value="0" />
            {/if}
            <input type="hidden" name="amount" value="{$amount}" />


            <input type="hidden" name="user_id" value="{$user->id}" />
            <input type="hidden" name="number" value="{$number}" />
            <input type="hidden" name="insure" value="{$insure}" />
            <input type="hidden" name="multipolis" value="{$multipolis}" />
            <input type="hidden" name="multipolis_amount" value="{$multipolis_amount}" />

            <input type="hidden" name="tv_medical" value="{$tv_medical}" />
            <input type="hidden" name="tv_medical_id" value="{$tv_medical_id}" />
            <input type="hidden" name="tv_medical_amount" value="{$tv_medical_amount}" />

            <input type="hidden" name="star_oracle" value="{$star_oracle}"/>
            <input type="hidden" name="star_oracle_id" value="{$star_oracle_id}"/>
            <input type="hidden" name="star_oracle_amount" value="{$star_oracle_amount}"/>
            <input type="hidden" name="action_type" value="{$action_type}"/>

            <input type="hidden" name="prolongation" value="{$prolongation}" />
            <input type="hidden" name="code_sms" value="{$code_sms}" />
            <input type="hidden" name="order_id" value="{$order_id}" />
            <input type="hidden" name="organization_id" value="{$organization_id}" />

            <input type="hidden" name="calc_percents" value="{$calc_percents}" />
            <input type="hidden" name="grace_payment" value="{$gracePayment}" />

            <input type="hidden" name="chdp" value="{$chdp}" />
            <input type="hidden" name="pdp" value="{$pdp}" />
            <input type="hidden" name="refinance" value="{$refinance}" />
            <input type="hidden" name="from" value="{$from}" />

            <div class="payment-block-loading"></div>

            <div class="payment-block-success js-payment-block-success">
                <p>Оплата прошла успешно</p> 
                <button  class="button big button-inverse cancel_payment" type="button">Продолжить</button>
            </div>
            <div class="payment-block-error">
                <p>Не удалось оплатить</p>
                <button  class="button big button-inverse cancel_payment" type="button">Продолжить</button>
            </div>

            <div class="payment-block-main">
                {if $sbp_enabled}
                    <p class="payment-block-title">Выберите способ оплаты</p>
                    <div class="payment-block-type-choice">
                        <div class="payment_types">
                            <div class="payment_type">
                                <img class="payment_type_image payment_type_spb active" src="design/{$settings->theme|escape}/img/payment_types/spb.svg" alt="spb"/>
                            </div>
                            <div class="payment_type">
                                <img class="payment_type_image payment_type_card" src="design/{$settings->theme|escape}/img/payment_types/card.svg" alt="card"/>
                            </div>
                        </div>
                    </div>
                {/if}

                <div style="display: none" id="payment-block-spb-type">

                </div>

                <div style="display: none" id="payment-block-card-type">
                    <p class="payment-block-title">Выберите карту для оплаты</p>

                    <ul class="payment-card-list">
                        {foreach $cards as $card}
                            <li>
                                <input type="radio" name="card_id" id="card_{$card->id}" value="{$card->id}" {if $card@first}checked="true"{/if} />
                                {*                        <input type="radio" name="card_id" id="card_{$card->id}" value="{$card->id}" {if $basicCard == $card->id}checked="true"{/if} />*}
                                <label for="card_{$card->id}">
                                    <strong>{$card->pan}</strong>
                                    <span>{$card->expdate}</span>
                                </label>
                                <br />
                            </li>
                        {/foreach}
                        <li>
                            <input type="radio" id="card_other" name="card_id" value="other" {if !$cards}checked="true"{/if} />
                            <label for="card_other"><strong>Другая карта</strong></label>
                        </li>
                    </ul>

                    {*}
                    <a href="#" target="_blank" class="button big" id="gpay" type="button"></a>
                    {*}
                </div>

                <div>
                    {if $settings->fake_dops}
                        <ul class="payment-dop-list">
                            <li>
                                <input type="checkbox" name="insurance_cart" value="1" checked="checked" id="insurance_cart"/>
                                <label for="insurance_cart" class="tooltip" id="insurance_label">Страхование карты
                                    <span id="insurance_price"></span> руб
                                    <span class="tooltip-icon">ℹ️
                                        <span class="tooltip-text">
                                            Услуга, которая позволяет застраховать вашу карту от 5-ти рисков.
                                        </span>
                                    </span>
                                </label>
                            </li>
                            <li>
                                <input type="checkbox" name="second_dop" value="1" checked="checked" id="second_dop"/>
                                {if $prolongation}
                                    <label for="second_dop" class="tooltip" id="oracle_label">
                                        "Звездный Оракул"
                                        <span id="second_dop_price"></span> руб
                                        <span class="tooltip-icon">ℹ️
                                            <span class="tooltip-text">
                                                Сервис объединяет возможности нейросетей для расшифровки сновидений, раскладов Таро, составления натальных карт и гороскопов.
                                            </span>
                                        </span>
                                    </label>
                                {else}
                                    <label for="second_dop" class="tooltip" id="second_dop_label">
                                        "Консьерж"
                                        <span id="second_dop_price"></span> руб
                                        <span class="tooltip-icon">ℹ️
                                            <span class="tooltip-text">
                                                Сервис, который обеспечивает быстрые и точные ответы на ваши вопросы в области юриспруденции и налогового законодательства.
                                            </span>
                                        </span>
                                    </label>
                                {/if}
                            </li>
                        </ul>
                    {/if}
                </div>

                <div class="payment-amount" id="paymentAmount">
                    {if $discount > 0 && $amount > $discount}
                        <s>{$amount} руб</s>
                        <br>
                        {$amount - $discount} руб
                    {else}
                        {$amount} руб
                    {/if}

                    {if $error}
                        <div class="error" style="font-size:1rem;color:#f11;">
                            {$error}
                        </div>
                    {/if}

                </div>

                <div class="payment-actions">
                    <p class="loading-text">
                        Подождите, пока выполняется запрос
                        <button class="button big button-inverse cancel_payment" type="button">Отмена</button>
                    </p>
                    <button  class="button big button-inverse cancel_payment" type="button">Отменить</button>
                    <a href="javascript:void(0)" class="button big" id="confirm_payment" type="button">Оплатить</a>
                </div>

            </div>

            {*if $have_exitpool}
                <div class="payment-block-exitpool">
                    <div class="payment-block-exitpool-success">Оплата прошла успешно</div>
                    <p class="payment-block-title">Скажите пожалуйста, по какой причине Вы не смогли оплатить заём вовремя?</p>
                    <p><small>Опрос анонимный</small></p>
                    <ul class="payment-card-list">
                        {foreach $exitpool_variants as $variant}
                        <li>
                            <input type="radio" id="payment_exitpool_{$variant->id}" name="payment_exitpool" value="{$variant->id}" />
                            <label for="payment_exitpool_{$variant->id}"><strong>{$variant->variant}</strong></label>
                        </li>
                        {/foreach}
                    </ul>
                    <button  class="button big button-inverse exitpool_button" type="button">Продолжить</button>
                </div>
            {/if*}

        </div>


	</div>
</section>

<div id="modal_fk_mult" class="white-popup mfp-hide">
    <div id="accept">
        <div class="modal-header">
            <a type="button" id="closeButtonModal" class="btn-close btn-close-modal pointer" data-bs-dismiss="modal" aria-label="Close">X</a>
        </div>
        <div class="">

            <p>"Консьерж" – это инновационный сервис, разработанный с использованием передовой системы искусственного интеллекта, который обеспечивает быстрые и точные ответы на ваши вопросы в области юриспруденции и
                налогового законодательства. Мы понимаем, как сложно может быть ориентироваться в юридических нюансах и процессах возврата налогов, и именно поэтому мы предлагаем решение, которое сэкономит ваше время и
                ресурсы.</p>

            <p>Программа представляет собой Telegram-бот, доступ к которому осуществляется путем ввода лицензионного ключа.</p>

            <p>ПО предназначено для</p>
            <ul>
                <li>Подбора оптимальных финансовых инструментов.</li>
                <li>Разработки стратегии достижения идеальной кредитной истории, на основе опроса пользователя.</li>
                <li>Обучения финансовой грамотности на кейсах непосредственного пользователя.</li>
                <li>Юридические консультации и предзаполненные шаблоны типовых документов под запрос пользователя.</li>
                <li>Доступ к нейросетям, обученным на основе технологий естественной обработки языка.</li>
            </ul>

            <p>Стоимость услуги: <span id="second_dop_price_modal"></span> руб</p>

            <p>За дополнительной информацией обращайтесь по {$config->org_phone}</p>

        </div>

    </div>
</div>

<div id="modal_fk_insurance" class="white-popup mfp-hide">
    <div id="accept">
        <div class="modal-header">
            <a type="button" id="closeButtonModal" class="btn-close btn-close-modal  pointer" data-bs-dismiss="modal" aria-label="Close">X</a>
        </div>
        <div class="">

            <h2>Защити свою карту от мошенничества! 🔒</h2>

            <p>В мире современных технологий, когда всё происходит так быстро, важно обеспечить безопасность ваших финансов. Наши услуги помогут защитить вашу карту от 5 основных рисков и мошеннических схем:</p>

            <ol>
                <li><strong>Оповещение</strong> о подозрительных операциях.</li>
                <li><strong>Защита от скимминга и фишинга.</strong></li>
                <li><strong>Обеспечение безопасности интернет-покупок.</strong></li>
                <li><strong>Мониторинг за необычной активностью.</strong></li>
                <li><strong>24/7 поддержка и консультации.</strong></li>
            </ol>

            <p><strong>Для заемщиков</strong> это особенно важно, ведь вы получите дополнительную уверенность в безопасности ваших средств. Преимущества нашего сервиса для вас:</p>

            <ul>
                <li>💡 <strong>Снижение финансовых рисков:</strong> ваши платежи защищены от несанкционированного использования.</li>
                <li>💡 <strong>Повышенная безопасность:</strong> минимизация вероятности кражи данных и мошеннических операций.</li>
                <li>💡 <strong>Экономия времени и нервов:</strong> наша команда экспертов всегда на страже ваших интересов.</li>
            </ul>

            <p><strong>Стоимость услуги: </strong><span id="insurance_price_modal"></span> руб</p>

            <p>Нас выбирают за надежность и оперативность. С нами ваши финансы будут под надежной защитой. Не откладывайте на потом, обеспечьте себе спокойствие уже сегодня! 🛡️🔐</p>

            <p>За дополнительной информацией обращайтесь по {$config->org_phone}</p>

        </div>

    </div>
</div>

<div id="modal_fk_oracle" class="white-popup mfp-hide">
    <div id="accept">
        <div class="modal-header">
            <a type="button" id="closeButtonModal" class="btn-close btn-close-modal  pointer" data-bs-dismiss="modal" aria-label="Close">X</a>
        </div>
        <div class="">

            <p>С помощью “Звездного Оракула” можно прогнозировать и управлять событиями своей жизни</p>

            <h2>4 ВИДА ГОРОСКОПА</h2>
            <ul>
                <li>Гороскоп</li>
                <li>Карты Таро</li>
                <li>Натальная карта</li>
                <li>Толкователь снов</li>
            </ul>

            <h3>Ежедневный гороскоп</h3>
            <p>Гороскоп на каждый день по знакам зодиака поможет вам определить свое поведение не только сегодня, но и на несколько дней вперед.</p>

            <h3>Карты таро</h3>
            <p>Система карт, каждая из которых обладает своим значением и помогает лучше понять прошлое, настоящее и будущее. Широко используется для предсказания будущего, самопознания и духовного развития.</p>

            <h3>Натальная карта</h3>
            <p>Карта, которая показывает положение планет на небе на момент рождения человека. Используется в астрологии для анализа характера и судьбы.</p>

            <h3>Толкователь снов</h3>
            <p>Снотолкователь предназначен для истолкования сновидений, а также для ониромантии (предсказания будущего по снам)</p>


            <strong><a target="_blank" href="/files/doc/oracul_key.pdf">Образец ключа</a></strong>
        </div>

    </div>
</div>

<script>
    setTimeout(() => {
        if ($('#click_event').val() == 1) {
            $('#confirm_payment').click();
        }
    }, 2000)

    const detailsButton = document.querySelector('#second_dop_label')
    const insuranceButton = document.querySelector('#insurance_label')
    const oracleButton = document.querySelector('#oracle_label')

    if (insuranceButton) {
      insuranceButton.addEventListener('click', (event) => {
        event.preventDefault()
        $.magnificPopup.open({
          items: {
            src: '#modal_fk_insurance',
          },
          showCloseBtn: true,
          type: 'inline',
        })

        const priceSpan = document.getElementById('insurance_price')
        const priceSpanModal = document.getElementById('insurance_price_modal')
        priceSpanModal.innerText = priceSpan.innerText
      })

    }
    
    if (detailsButton) {
      detailsButton.addEventListener('click', (event) => {
        event.preventDefault()
        $.magnificPopup.open({
          items: {
            src: '#modal_fk_mult',
          },
          showCloseBtn: true,
          type: 'inline',
        })

        const priceSpan = document.getElementById('second_dop_price')
        const priceSpanModal = document.getElementById('second_dop_price_modal')
        priceSpanModal.innerText = priceSpan.innerText
      })
    }

    if (oracleButton) {
      oracleButton.addEventListener('click', (event) => {
        event.preventDefault()
        $.magnificPopup.open({
          items: {
            src: '#modal_fk_oracle',
          },
          showCloseBtn: true,
          type: 'inline',
        })
      })
    }

    document.addEventListener('DOMContentLoaded', function () {
      const insuranceCheckbox = document.getElementById('insurance_cart')
      const creditRatingCheckbox = document.getElementById('second_dop')
      const paymentAmountDiv = document.getElementById('paymentAmount')
      const discount = parseFloat(document.getElementById('collection_promo').value);
      const insurancePriceSpan = document.getElementById('insurance_price')
      const creditRatingPriceSpan = document.getElementById('second_dop_price')
      const closeModalButtons = document.getElementsByClassName('btn-close-modal')
      let baseAmount = parseFloat(paymentAmountDiv.innerText) - discount;

      const fakeDopLimit = 5000
      let insuranceCost = Math.min(baseAmount * 0.2, fakeDopLimit)
      let creditRatingCost = Math.min(baseAmount * 0.3, fakeDopLimit)


      function updateAmount () {
        let newAmount = baseAmount
        if (insuranceCheckbox.checked) newAmount += insuranceCost
        if (creditRatingCheckbox.checked) newAmount += creditRatingCost
        if (discount > 0) {
            let amountWithoutDiscount = (newAmount + discount).toFixed(2);
            let amountWithDiscount = newAmount.toFixed(2);
            paymentAmountDiv.innerHTML = '<s>' + amountWithoutDiscount + ' руб</s> <br> ' + amountWithDiscount + ' руб';
        } else {
            paymentAmountDiv.innerHTML = newAmount.toFixed(2) + ' руб'
        }
      }

      if (insuranceCheckbox) {
        insurancePriceSpan.innerText = insuranceCost.toFixed(2)
        insuranceCheckbox.addEventListener('change', updateAmount)
      }
      if (creditRatingCheckbox) {
        creditRatingCheckbox.addEventListener('change', updateAmount)
        creditRatingPriceSpan.innerText = creditRatingCost.toFixed(2)
      }

      Array.from(closeModalButtons).forEach(button => {
        button.addEventListener('click', function () {
          $.magnificPopup.close()
        })
      })

      if (insuranceCheckbox || creditRatingCheckbox) {
        updateAmount()
      }

    })
</script>
