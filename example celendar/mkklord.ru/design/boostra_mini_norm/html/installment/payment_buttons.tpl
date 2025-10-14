{$pdp = $order_data->balance->details['ОбщийДолг'] - $order_data->balance->details['Баланс']}
{$next_payment = $order_data->balance->details['БлижайшийПлатеж_Сумма'] + $order_data->balance->details['ПросроченныйДолг']}

<div class="js-il-payment-buttons" 
    data-pdp="{$pdp}" 
    data-need-accept="{$order_data->balance->need_accept}" 
    data-next-payment="{$next_payment}" 
    data-phone="{$user->phone_mobile}"
    data-contract-number="{$order_data->balance->zaim_number}"
    data-contract-date="{$order_data->balance->zaim_date}"
    data-user-id="{$user->id}" 
>
    
    {if $order_data->balance->details['БлижайшийПлатеж_Дата'] && $order_data->balance->details['Баланс'] < $order_data->balance->details['БлижайшийПлатеж_Сумма']}

    <form method="POST" action="user/payment" style="margin-top:15px;" class="user_payment_form" >
        <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
        <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
    
        <div class="action">
            <input style="display:none" class="payment_amount" data-order_id="{$order_data->balance->zaim_number}"
                   data-user_id="{$user->id}" type="text" name="amount"
                   value="{$next_payment}" />
            <button class="payment_button green button medium js-save-click" data-user="{$user->id}" data-event="10" type="submit">
                Оплатить текущий платеж {$next_payment} руб
            </button>
        </div>
    </form>
    {/if}
    
    <div class="user_payment_form">
        <div class="action">
            <button class="payment_button button medium button-inverse js-save-click" data-user="{$user->id}" data-event="11" onclick="$('#other_summ_{$order_data_index}').fadeIn('fast');$(this).hide()" type="button">
                Оплатить любую сумму
            </button>
        </div>
    </div>
    <form method="POST" action="user/payment" id="other_summ_{$order_data_index}" class="user_payment_form js-il-chdp-form" style="display:none;padding:40px 0;">
        <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
        <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
        <input type="hidden" name="sms" value="" />
        <div class="action">
            <input class="payment_amount js-il-chdp-amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount"
                   value="{$next_payment}" max="{$next_payment}" data-rec="{$next_payment}" min="1" />
            <button class="payment_button button medium js-save-click js-il-chdp-button" data-user="{$user->id}" data-event="12" type="button">Оплатить</button>
            <span class="js-il-chdp-amount-error il-chdp-amount-error">&nbsp;</span>
        </div>
        <div class="js-il-chdp-checkbox-block" style="display:none">
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 14px !important;height: 14px !important;">
                    <input class="js-il-chdp-checkbox" type="checkbox" value="1"
                           id="chdp_{$order_data_index}"
                           name="chdp" />
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
                <a href="#" data-href="preview/chdp" target="_blank" class="js-il-document-link">
                    Заявление на частичное доcрочное погашение
                </a>
            </label>
    
        </div>
        <div class="js-il-chdp-accept-block" style="display:none">
            <p></p>
            
            <div style="padding-bottom:10px">
                <input type="text" name="sms_code" class="js-il-chdp-code" value="" placeholder="Код из СМС" />
                <div class="js-il-chdp-code-error" style="color:red"></div>
                <br />
                <a href="javascript:void(0);" class="js-il-chdp-code-repeat">отправить код еще раз</a>
            </div>
            <div style="margin-top:10px;">
                <button class="button medium js-il-chdp-code-button" type="button">
                    Перейти к оплате
                </button>
            </div>

        </div>
    </form>


    <div class="user_payment_form"  style="display:none;padding:40px 0;">
        <div class="action">
            <button class="payment_button button button-inverse js-save-click" data-user="{$user->id}" data-event="13" type="button">
                Погасить заём досрочно {$pdp} руб
            </button>
        </div>
    </div>

    <form method="POST" action="user/payment" id="other_summ_{$order_data_index}" class="user_payment_form js-il-pdp-form" style="">
        <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
        <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
        <input type="hidden" name="sms" value="" />
        <div class="action">
            <input class="payment_amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" 
                type="hidden" name="amount"
                value="{$pdp}" max="{$pdp}" data-rec="{$order_data->balance->details['БлижайшийПлатеж_Сумма']}" min="1" />
            <button class="payment_button button medium button-inverse js-save-click js-il-pdp-button" data-user="{$user->id}" data-event="12" type="button">
                Погасить заём {$pdp} руб
            </button>
        </div>
        <div class="js-il-pdp-accept-block" style="display:none">
            <p></p>
            
            <div style="padding-bottom:10px">
                <input type="text" name="sms_code" class="js-il-pdp-code" value="" placeholder="Код из СМС" />
                <div class="js-il-pdp-code-error" style="color:red"></div>
                <br />
                <a href="javascript:void(0);" class="js-il-pdp-code-repeat">отправить код еще раз</a>
            </div>
            <label class="spec_size" style="">
                <div class="checkbox"
                     style="border-width: 1px;width: 14px !important;height: 14px !important;">
                    <input class="" type="checkbox" value="1"
                           id="pdp_{$order_data_index}"
                           name="pdp" checked="" readonly=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
                <a href="#" data-href="preview/pdp" target="_blank" class="js-il-document-link">
                    Заявление на полное доcрочное погашение
                </a>
            </label>
            <div style="margin-top:10px;">
                <button class="button medium js-il-pdp-code-button" type="button">
                    Перейти к оплате
                </button>
            </div>

        </div>
    </form>
</div>