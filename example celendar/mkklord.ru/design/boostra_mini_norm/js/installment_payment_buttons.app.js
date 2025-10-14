function InstallmentPaymentButtonsApp($block)
{
    var app = this;
    
    function _init(){
        app.pdp = $block.data('pdp');
        app.chdp = $block.data('chdp');
        app.need_accept = $block.data('need-accept');
        app.next_payment = $block.data('next-payment');
        app.phone = $block.data('phone');
    };
    
    function _init_documents(){
        $block.find('.js-il-document-link').click(function(e){
            if (!$(this).hasClass('ready')) {
            
                var user_id = $block.data('user-id'),
                    href = $(this).data('href'),
                    contract_number = $block.data('contract-number'),
                    contract_date = $block.data('contract-date'),
                    payment_amount = $block.find('.js-il-chdp-amount').val();
                
                var link = href
                    +'?user_id='+user_id
                    +'&params[contract_number]='+contract_number
                    +'&params[contract_date]='+contract_date
                    +'&params[payment_amount]='+payment_amount;
                $(this).addClass('ready').attr('href', link);

                return true;
            }
        });
    };
    
    function _init_chdp(){
        var $chdp_form = $block.find('.js-il-chdp-form')
        var $chdp_button = $block.find('.js-il-chdp-button');
        var $chdp_accept_block = $block.find('.js-il-chdp-accept-block');
        $chdp_button.click(function(){
            if (!app.need_accept) {
                $chdp_form.submit();
            } else if (app.need_accept && !$block.find('.js-il-chdp-checkbox').is(':checked')) {
                $chdp_form.submit();
            } else {
                $block.find('.js-il-chdp-button').hide();
                $chdp_accept_block.show();
                _send_chdp_sms();
            }
        })
        
        $block.find('.js-il-chdp-code-repeat').click(function(e){
            e.preventDefault();
            if (!$(this).hasClass('inactive')) {
                _send_chdp_sms();
            }
        });
        
        $block.find('.js-il-chdp-code-button').click(function(){
            if (!$(this).hasClass('loading')) {
                _check_chdp_sms();
            }
        });
        
        $block.find('.js-il-chdp-amount').blur(function(){
            _check_chdp_amount();
        });
        $block.find('.js-il-chdp-amount').keyup(function(){
            _check_chdp_amount();
        });
    };
    
    function _check_chdp_amount(){
        var current_amount = parseFloat($block.find('.js-il-chdp-amount').val());
        var rec_amount = parseFloat($block.find('.js-il-chdp-amount').data('rec'));
        
        if (current_amount < rec_amount) {
            $block.find('.js-il-chdp-checkbox-block').hide()
            $block.find('.js-il-chdp-checkbox').attr('checked', false);;            
            $block.find('.js-il-chdp-amount-error').html('Суммы не хватит для погашения текущего платежа');
        } else if (current_amount > rec_amount) {
            if (app.need_accept) {
                $block.find('.js-il-chdp-checkbox-block').show();
            }
            $block.find('.js-il-chdp-amount-error').html("&nbsp;");
        } else {
            $block.find('.js-il-chdp-checkbox-block').hide();
            $block.find('.js-il-chdp-checkbox').attr('checked', false);;            
            $block.find('.js-il-chdp-amount-error').html("&nbsp;");
        }
    };
    
    function _init_pdp(){
        var $pdp_form = $block.find('.js-il-pdp-form');
        var $pdp_button = $block.find('.js-il-pdp-button');
        var $pdp_accept_block = $block.find('.js-il-pdp-accept-block');
        $pdp_button.click(function(){
            if (!app.need_accept) {
                $pdp_form.submit();
            } else {
                $pdp_accept_block.show();
                _send_pdp_sms();
            }
        })
        
        $block.find('.js-il-pdp-code-repeat').click(function(e){
            e.preventDefault();
            if (!$(this).hasClass('inactive')) {
                _send_pdp_sms();
            }
        });
        
        $block.find('.js-il-pdp-code-button').click(function(){
            if (!$(this).hasClass('loading')) {
                _check_pdp_sms();
            }
        })
        
    };

    function _check_pdp_sms(){
        var _data = {
            action: 'check',
            phone: app.phone,
            code: $block.find('.js-il-pdp-code').val(),
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $block.find('.js-il-pdp-code-button').addClass('loading')
            },
            success: function(resp){
                if (resp.success)
                {
                    $block.find('.js-il-pdp-form').submit();
                }
                else
                {
                    // код не совпадает
                    if (resp.accept_try == 1) {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                    } else if (resp.accept_try > 1) {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                    } else {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает').show();                        
                    }
                    $block.find('.js-il-pdp-code-button').removeClass('loading')
                }
            }

        });
    
    }
    
    function _send_pdp_sms(){
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: app.phone,
                flag: 'АСП'
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        app.set_pdp_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    app.set_pdp_timer(resp.time_left);

                    if (!!resp.developer_code)
                        $block.find('.js-il-pdp-code').val(resp.developer_code);
                }
            }
        });
        
    }
    
    function _check_chdp_sms(){
        var _data = {
            action: 'check',
            phone: app.phone,
            code: $block.find('.js-il-chdp-code').val(),
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $block.find('.js-il-chdp-code-button').addClass('loading')
            },
            success: function(resp){
                if (resp.success)
                {
                    $block.find('.js-il-chdp-form').submit();
                }
                else
                {
                    // код не совпадает
                    if (resp.accept_try == 1) {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                    } else if (resp.accept_try > 1) {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                    } else {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает').show();                        
                    }
                    $block.find('.js-il-chdp-code-button').removeClass('loading')
                }
            }

        });
    
    }
    
    function _send_chdp_sms(){
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: app.phone,
                flag: 'АСП'
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        app.set_chdp_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    app.set_chdp_timer(resp.time_left);

                    if (!!resp.developer_code)
                        $block.find('.js-il-chdp-code').val(resp.developer_code);
                }
            }
        });
        
    }
    
    app.set_pdp_timer = function(_seconds){

        clearInterval(app.pdp_timer);

        app.pdp_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var _str = '<span>Повторно отправить код можно через '+_seconds+'сек</span>';
                $block.find('.js-il-pdp-code-repeat').addClass('inactive').html(_str).show();
            }
            else
            {
                $block.find('.js-il-pdp-code-repeat').removeClass('inactive').html('Отправить код еще раз').show();

                clearInterval(app.pdp_timer);
            }
        }, 1000);

    };

    app.set_chdp_timer = function(_seconds){

        clearInterval(app.chdp_timer);

        app.chdp_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var _str = '<span>Повторно отправить код можно через '+_seconds+'сек</span>';
                $block.find('.js-il-chdp-code-repeat').addClass('inactive').html(_str).show();
            }
            else
            {
                $block.find('.js-il-chdp-code-repeat').removeClass('inactive').html('Отправить код еще раз').show();

                clearInterval(app.chdp_timer);
            }
        }, 1000);

    };

    ;(function(){
        _init();
        _init_chdp();
        _init_pdp();
        _init_documents();
    })();
}
$(function(){
    $('.js-il-payment-buttons').each(function(){
        new InstallmentPaymentButtonsApp($(this));
    })
})