<style>
    .il-chdp-amount-error {
        display: block;
        color: #f22;
    }
</style>
<div class="split">
    <ul>
        <li>
            <div>Общая сумма долга</div>
            <div>
                {$user_balance->details['ОбщийДолг']}
            </div>
        </li>
        {if $user_balance->details['Баланс']}
        <li>
            <div>Сумма на балансе</div>
            <div>
                {$user_balance->details['Баланс']}
            </div>
        </li>
        {/if}
        <li>
            <div>
                Очередной платеж
                {if $user_balance->details['ПросроченныйДолг'] > 0}
                <br /><small class="red">(с учетом просроченного платежа {$user_balance->details['ПросроченныйДолг']} руб.)</small>
                {/if}
                <br />
                <a href="user/schedule_payments/{$user_balance->zaim_number}">График платежей</a>
            </div>
            <div>
                {if $user_balance->details['БлижайшийПлатеж_Сумма'] > 0 && $user_balance->details['Баланс'] >= $user_balance->details['БлижайшийПлатеж_Сумма']}
                    Оплачен
                {else}
                    {$user_balance->details['БлижайшийПлатеж_Сумма'] + $user_balance->details['ПросроченныйДолг']}
                {/if}
            </div>
        </li>
        <li>
            {if $user_balance->details['БлижайшийПлатеж_Дата']}
            <div>Дата очередного платежа</div>
            <div>{$user_balance->details['БлижайшийПлатеж_Дата']|date}</div>
            {else}
            <div class="red text-left">Ваш заём просрочен</div>
            {/if}
        </li>
    </ul>
</div>