function CalculateIL()
{
    var app = this;
    app.time_values = [];
    
    app.max_pdl_period = 16; // max pdl days
    app.max_pdl_amount = 30000;

    app.min_instl_period = 42; // min il days
    app.min_instl_amount = 1000;
    app.instl_interval = 14; // интервал в днях для инстолментов
    
    function _init(){
        app.$money_range = $("#money-range");
        app.$time_range = $("#time-range");    
    }

    var _init_money_range = function(){
    	app.$money_range.ionRangeSlider({
    		type: "single",
    		min: calculator_il_params.min_amount,
    		max: calculator_il_params.max_amount,
    		from: calculator_il_params.default_amount,
            step: 1000,
    		postfix: " <span>Р</span>",
    		onChange: function (data) {
    			app.check_money_slider(data);
                app.calculate();
    		}
    	});
        
    };
    
    var _init_time_range = function(){
    	app.$time_range.ionRangeSlider({
    		type: "single",
    		postfix: "",
    		onChange: function (data) {
    			app.check_time_slider(data);
                app.calculate();
    		},
            values: app.time_values,
    		from: app.array_search(calculator_il_params.default_period, app.time_values),
            prettify(position) {
                position = parseInt(position)
                if (position >= app.min_instl_period) {
                    var _weeks = Math.round(Math.round(position/app.instl_interval)*app.instl_interval/7);
                    return _weeks + _create_label(_weeks, [' Неделя', ' Недели', ' Недель']);
                } else {
                    return position + _create_label(position, [' День', ' Дня', ' Дней']);
                }
            }
        });
        app.$time_range.data('ionRangeSlider').update({
    		from: app.array_search(calculator_il_params.default_period, app.time_values),
        });
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
        if (period < app.min_instl_period && amount > app.max_pdl_amount) {
            app.$money_range.data('ionRangeSlider').update({
                from: app.max_pdl_amount
            });
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
			percent = parseFloat(BASE_PERCENTS) / 100,
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

		if (period > app.max_pdl_period) {


            var period_percent = percent * app.instl_interval;
            var payments_count = period / app.instl_interval;
            var everypayment = Math.ceil(amount * period_percent / (1 - Math.pow(1 + period_percent, -payments_count)));

            var period_percent = percent * app.instl_interval;
            var payments_count = period / app.instl_interval;
            var coef = Math.pow(1 + period_percent, payments_count) / (Math.pow(1 + period_percent, payments_count) - 1);
            var everypayment = Math.ceil(amount * period_percent * coef); 

            $('.period .irs-single, .period .irs-min').removeClass('green');
            $("#calculator .result, .calculate_green").removeClass('green');
            $('.period .irs-slider.single').removeClass('bg-green');
            $('.discount_title, .discount_subtitle').removeClass('green').addClass('red');
            $('.main-page-button').text('Получить займ');

            var result_text = 'Платеж <span class="total">'+everypayment+'</span> руб. раз в 2 недели до <span class="date">'+(payDate.getDate() + ' ' + month)+'</span>';
            $("#calculator .result").html(result_text);                
            
            return;
            
		} else {
            
		}
        
        if (period > discount_period)
        {
            var total = amount * period * percent + amount;
        }
        else
        {
            var total = amount * period * discount_percent + amount;
        }
        
		
        
		if (period > discount_period)
        {
            $('.period .irs-single, .period .irs-min').removeClass('green');
            $("#calculator .result, .calculate_green").removeClass('green');
            $('.period .irs-slider.single').removeClass('bg-green');
            $('.discount_title, .discount_subtitle').removeClass('green').addClass('red');
            $('.main-page-button').text('Получить займ');
        }
        else
        {
            if (location.pathname !== '/user' || $('[name="has_user_discount"]').length) {
                $('.period .irs-single').addClass('green');
            }

            $("#calculator .result, .calculate_green, .period .irs-min").addClass('green');
            $('.period .irs-slider.single').addClass('bg-green');
            $('.discount_title, .discount_subtitle').addClass('green').removeClass('red');
            $('.main-page-button').text('Получить бесплатно');
        }

        var result_text = 'К возврату <span class="total">'+total+'</span> руб. до <span class="date">'+(payDate.getDate() + ' ' + month)+'</span>';
        $("#calculator .result").html(result_text);                
	}

    var _create_label = function(number, titles){
        const cases = [2, 0, 1, 1, 1, 2];
        return `${titles[number % 100 > 4 && number % 100 < 20 ? 2 : cases[number % 10 < 5 ? number % 10 : 5]]}`;
    }

    function _run(){
        if (app.$money_range.length == 0)
            return false;
        
        _init_time_values();
        _init_money_range();
        _init_time_range();
    
        app.calculate();
    }
    
    (function(){
        _init();
        _run();
    })();
}
new CalculateIL();

