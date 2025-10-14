<div id="edit-amount">
    <h4 class="text-orange">Вы можете изменить сумму займа </h4>
    <div class="slider-box">
        <div class="money-edit">
            <span class="edit-amount-value">1000</span>
            <span class="ion-btn ion-minus ion-il"></span>
            <div>
                {*  !!!
                    Если меняете логику калькулятора - поменяйте и её проверку в UserView (edit_amount action)
                 *}
                <input type="text"
                       id="money-edit"
                       name="amount_edit"
                       data-max="{$user_order['approve_max_amount']}"
                       data-min="1000"
                       data-step="1000"
                       data-init_value="{$user_order['approved_amount']}"
                       value="{$user_order['amount']}" />
            </div>
            <span class="ion-btn ion-plus ion-il"></span>
            <span class="edit-amount-value">{$user_order['approve_max_amount']}</span>
        </div>
        <div class="time-edit">
            <span class="edit-period-value">5 дней</span>
            <span class="ion-btn ion-minus ion-il"></span>
            <div>
                <input type="text"
                       id="time-edit"
                       name="period_edit"
                       data-init_value="{$user_order['period']}"
                       value="{$user_order['period']}" />
            </div>
            <span class="ion-btn ion-plus ion-il"></span>
            <span class="edit-period-value">{($user_order['max_period']/7)} {($user_order['max_period']/7)|plural:' Неделя':' Недель':' Недели'}</span>
        </div>
        <div
            id="full-loan-info"
            data-percent="{$user_order['percent']}"
            data-period="{$user_order['period']}"
            data-amount="{$user_order['amount']}"
            data-promocode="{$user_order['promocode']}"
            style="font-size: 2rem!important;"
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
            #edit-amount, #edit-time {
                max-width: 720px;
            }
            #edit-amount .money-edit, #edit-amount .time-edit {
                display: grid;
                grid-template: 1fr / auto auto 1fr auto auto;
                align-items: center;
                grid-gap: 15px;
            }
            @media screen and (max-width: 992px) {

            }
        </style>
    {/literal}
    {capture name=page_scripts}
        <script>
    var calculator_il_params = {
        default_period: 16,
        min_period: 5, 
        max_period: {$user_order['max_period']},
        default_amount: 30000,
        min_amount: 1000,
        max_amount: 100000,
    };
            {literal}
function CalculateIL()
{
    var app = this;
    app.time_values = [];
    
    app.max_pdl_period = 16; // max pdl days
    app.max_pdl_amount = 30000;

    app.min_instl_period = {/literal}{$user_order['min_period']}{literal}; // min il days
    app.min_instl_amount = 30000;
    app.instl_interval = 14; // интервал в днях для инстолментов
    
    function _init(){
        app.$money_range = $("#money-edit");
        app.$time_range = $("#time-edit");    
        app.$loan_info = $('#full-loan-info');
    }

    var _init_money_range = function(){
    	app.$money_range.ionRangeSlider({
    		type: "single",
    		min: calculator_il_params.min_amount,
    		max: calculator_il_params.max_amount,
    		from: app.$loan_info.data('amount'),
            hide_min_max: true,
            step: 1000,
    		postfix: " <span>Р</span>",
    		onChange: function (data) {
    			app.check_money_slider(data);
                app.calculate();
    		}
    	});
        
    };
    
    var _init_time_range = function(){
        if (app.min_instl_period > 0 ) {
        	app.$time_range.ionRangeSlider({
        		type: "single",
        		postfix: "",
        		onChange: function (data) {
        			app.check_time_slider(data);
                    app.calculate();
        		},
                hide_min_max: true,
                values: app.time_values,
        		from: app.array_search(app.$loan_info.data('period'), app.time_values),
                prettify(position) {
                    position = parseInt(position)
                    if (app.min_instl_period > 0 && position >= app.min_instl_period) {
                        var _weeks = Math.round(Math.round(position/app.instl_interval)*app.instl_interval/7);
                        return _weeks + _create_label(_weeks, [' Неделя', ' Недели', ' Недель']);
                    } else {
                        return position + _create_label(position, [' День', ' Дня', ' Дней']);
                    }
                }
            });
            app.$time_range.data('ionRangeSlider').update({
        		from: app.array_search(app.$loan_info.data('period'), app.time_values),
            });
        } else {
            $('.time-edit').hide();
        }
    };
    
    app.check_money_slider = function(data){

		var amount = parseInt(app.$money_range.val()),
			period = parseInt(app.$time_range.val());

        if (amount > app.max_pdl_amount && period < app.min_instl_period) {
            app.$time_range.data('ionRangeSlider').update({
                from: app.array_search(app.min_instl_period, app.time_values)
            });
        }
        if (amount < app.max_pdl_amount && period > app.max_pdl_period) {
            app.$time_range.data('ionRangeSlider').update({
                from: app.array_search(app.max_pdl_period, app.time_values)
            });
        }
    }
    
    app.check_time_slider = function(data){
		var amount = parseInt(app.$money_range.val()),
			period = parseInt(app.$time_range.val());

        if (period > app.max_pdl_period && amount < app.min_instl_amount) {
            app.$money_range.data('ionRangeSlider').update({
                from: app.min_instl_amount
            });
        }
        if (app.min_instl_period > 0) {
            if (period < app.min_instl_period && amount > app.max_pdl_amount) {
                app.$money_range.data('ionRangeSlider').update({
                    from: app.max_pdl_amount
                });
            }
        }
    }

    app.array_search = function(needle, haystack) {	
    	for(var key in haystack){
    		if(haystack[key] == needle){
    			return key;
    		}
    	}
    	return false;
    }
    
    _init_time_values = function(){
        var days_max = Math.min(calculator_il_params.max_period, app.max_pdl_period);
        for (var i = calculator_il_params.min_period; i <= days_max; i++) {
            app.time_values.push(i); 
        }
        if (calculator_il_params.max_period > app.max_pdl_period) {
            for (var w = app.min_instl_period; w <= calculator_il_params.max_period; w += app.instl_interval) {
                app.time_values.push(w); 
            }
        }
    }
    
	app.calculate = function(){
		var amount = parseInt(app.$money_range.val()),
			period = parseInt(app.$time_range.val()),
			percent = parseFloat(app.$loan_info.data('percent')) / 100,
			discount_percent = parseFloat($("#percent").val()),
			discount_period = parseInt($("#max_period").val()),
			have_close_credits = parseInt($("#have_close_credits").val()),
			now = new Date;

        var payDate = new Date;

		payDate.setDate(now.getDate() + parseInt(period));
		var month = [
                'января',   'февраля', 'марта',  'апреля',
                'мая',      'июня',    'июля',   'августа',
                'сентября', 'октября', 'ноября', 'декабря'
            ][payDate.getMonth()];

        
        if (parseInt(app.$money_range.val()) === parseInt(app.$money_range.data('init_value')) 
            && parseInt(app.$time_range.val()) === parseInt(app.$time_range.data('init_value'))
        ) {
            $("#accept_edit_amount").hide();
        } else {
            $("#accept_edit_amount").show();
        }


		if (period > app.max_pdl_period) {

            var period_percent = percent * app.instl_interval;
            var payments_count = period / app.instl_interval;
            var coef = Math.pow(1 + period_percent, payments_count) / (Math.pow(1 + period_percent, payments_count) - 1);
            var everypayment = Math.ceil(amount * period_percent * coef); 

            var result_text = 'Платеж <span class="total">'+everypayment+'</span> руб. раз в 2 недели до <span class="date">'+(payDate.getDate() + ' ' + month)+'</span>';
            $("#full-loan-info").html(result_text);                
            
            return true;
		}
        
        var total = amount * period * percent + amount;
        var result_text = 'К возврату <span class="total">'+total+'</span> руб. до <span class="date">'+(payDate.getDate() + ' ' + month)+'</span>';
        $("#full-loan-info").html(result_text);                
	}

    var _create_label = function(number, titles){
        const cases = [2, 0, 1, 1, 1, 2];
        return `${titles[number % 100 > 4 && number % 100 < 20 ? 2 : cases[number % 10 < 5 ? number % 10 : 5]]}`;
    }

    $("#accept_edit_amount").on('click', function () {
        let edit_amount = parseInt($("#money-edit").val());
        let edit_period = parseInt($("#time-edit").val());
        let order_id = $(this).data('order');
        $("body").addClass('is_loading');

        $.post('/user?action=edit_amount', {
            'edit_amount': edit_amount,
            'edit_period': edit_period,
            'order_id': order_id
        }).done(function(json) {
            if (json.result) {
                location.reload();
            }
        });
    });


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
    var _init_plus_minus = function(){
        $(".ion-btn").on('click', function (){
//            let sliderElement = $(this).closest('div').find('input.irs-hidden-input').data('ionRangeSlider'),
//                isPlus = $(this).hasClass('ion-plus'),
//                step = sliderElement.options.step,
//                value = sliderElement.old_from,
//                newValue = isPlus ? value + step : value - step;
//    
//            sliderElement.update({from: newValue})
            
            app.calculate();
        });
    }

    function _run(){
        if (app.$money_range.length == 0)
            return false;
        
        _init_time_values();
        _init_money_range();
        _init_time_range();
        _init_plus_minus();
    
        app.calculate();
        
        changeSliderStyles();
    }
    
    (function(){
        _init();
        _run();
    })();
}
new CalculateIL();



            

            {/literal}

        </script>
        {*Отправка метрики по кнопке получить займ в ЛК в зависимости от типа клиента https://trello.com/c/oL2cPB2c*}
        <script>
            $('#open_accept_modal').click(function(){
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
    {/capture}
</div>