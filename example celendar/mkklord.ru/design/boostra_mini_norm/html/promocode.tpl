{if $promo_block !== Promocodes::MODE_NONE}
    <style>
        .promocodes a:hover {
            text-decoration: none;
        }
    </style>
    <div class="promocodes">
        {if $promo_block === Promocodes::MODE_APPLY}
            <div style="font-style: normal; color: #959595;">
                Вы применили промокод
            </div>
        {else}
            <div>
                <a
                    href="#"
                    id="promo-title-link"
                    onclick="return onPromoTitleClick(this);"
                    style="font-style: normal;
                            font-weight: 700;
                            color: #000000;
                            font-size: 1.45rem;
                            border-bottom: 3px #000000 solid;
                            box-sizing: border-box;"
                >У меня есть промокод</a>
            </div>
            <div id="promocode-applied" style="font-style: normal; color: #959595; display: none;">
                Вы применили промокод
            </div>
            {if $promo_block === Promocodes::MODE_BANNER}
                <div class="promo-block" style="display: none; font-size: 18px; margin-top: 10px;">
                    Поздравляем! Вам доступны льготные условия займа! Отправьте заявку на заём.
                    <br/>После её одобрения у Вас появится возможность применить промокод.
                </div>
                <script>
                    function onPromoTitleClick(elem) {
                        $('.promo-block').show();
                        return false;
                    }
                </script>
            {elseif $promo_block === Promocodes::MODE_FORM}
                <div class="promo-block" style="display: none;">
                    <input
                        value="{if $smarty.cookies.promocode}{$smarty.cookies.promocode}{/if}"
                        type="text"
                        name="promocode"
                        placeholder="Введите промокод"
                        style="border: 2px solid #000;
                                border-radius: 0.5rem;
                                padding: 0.8rem;
                                text-align: center;
                                margin-right: 15px;
                                text-transform: uppercase;"
                    />
                    <button
                        class="big"
                        style="border-radius: 1rem; background-color: #ff0000; border: none;"
                        onclick="applyPromocode(); return false;"
                    >Применить</button>
                </div>
                <div id="promocode-alert" style="font-style: normal; color: #f00; display: none;">
                    Промокод отсутствует в системе
                </div>
                <script>
                    function onPromoTitleClick(elem) {
                        $('.promo-block').show();
                        $(elem).hide();
                        return false;
                    }
                    function applyPromocode() {
                        var code = document.querySelector('input[name="promocode"]').value.trim();
                        var info_field = document.querySelector('#full-loan-info');
                        var old_contract   = document.querySelector('#old_contract');
                        var gray_contract  = document.querySelector('#gray_contract');
                        var green_contract = document.querySelector('#green_contract');

                        if(code) {
                            $("body").addClass('is_loading');
                            $.post('/ajax/promocodes.php', { code: code })
                            .done(function(json) {
                                if (json.success) {
                                    if(info_field) {
                                        info_field.dataset.percent   = json.promocode.percent;
                                        info_field.dataset.promocode = json.promocode.id;

                                        changeSliderStyles();
                                        updateFullLoanInfo(document.querySelector('#money-edit').value)
                                    }
                                    if(old_contract) {
                                        var link_parts = old_contract.href.split('/');
                                        link_parts.pop();
                                        link_parts.pop();
                                        link_parts.push(json.promocode.contract);
                                        if(old_contract) {
                                            old_contract.href = link_parts.join('/');
                                        }
                                        if(gray_contract) {
                                            gray_contract.href = link_parts.join('/');
                                        }
                                        if(green_contract) {
                                            green_contract.href = link_parts.join('/');
                                        }
                                    }

                                    $('.promo-block').hide();
                                    $('#promo-title-link').hide();
                                    $('#promocode-alert').hide();
                                    $('#promocode-applied').show();
                                } else {
                                    document.querySelector('input[name="promocode"]').style.borderColor = "#f00";
                                    document.querySelector('input[name="promocode"]').style.color = "#f00";
                                    $('#promocode-alert').show();
                                }
                                $("body").removeClass('is_loading');
                            });
                        }
                    }

                    {if $smarty.cookies.promocode}
                        $(document).ready(function () {
                            applyPromocode();
                        });
                    {/if}
                </script>
            {/if}
        {/if}
    </div>
{/if}