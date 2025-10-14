{* Страница заказа *}

{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}

{literal}
    <script>
        window.inactivityPopupEnabled = false;

        {/literal}
        {if $is_short_flow}
            const TIMER_INIT = 90; // 1.5 минуты
        {else}
            const TIMER_INIT = 180; // 3 минуты
        {/if}
        {literal}

        $(function(){
            // Начальное время в секундах
            let totalTime = $.cookie('bnn-timer') ?? TIMER_INIT;
            if (totalTime > TIMER_INIT)
                totalTime = TIMER_INIT;
            $('#js-wait-scorings-timer').text(formatTime(totalTime));
            updateProgressBar();

            // Функция для форматирования времени в формате MM:SS
            function formatTime(seconds) {
                let minutes = Math.floor(seconds / 60);
                let remainingSeconds = seconds % 60;

                return (
                    String(minutes).padStart(2, '0') + ':' + String(remainingSeconds).padStart(2, '0')
                );
            }

            function updateProgressBar() {
                {/literal}{if $is_short_flow}{literal}
                if (totalTime < 23)
                    setProgressBar(98);
                else if (totalTime < 41)
                    setProgressBar(93);
                else if (totalTime < 54)
                    setProgressBar(91);
                else if (totalTime < 63)
                    setProgressBar(89);
                else if (totalTime < 70)
                    setProgressBar(86);
                else if (totalTime < 75)
                    setProgressBar(83);
                else if (totalTime < 78)
                    setProgressBar(82);
                {/literal}{/if}{literal}
            }

            // Проверка готовности скорингов
            function checkScorings(isTimeOut = false) {
                $.ajax({
                    url: '/ajax/check_scorings_nk.php',
                    data: {
                        action: 'check',
                        timeout: isTimeOut
                    },
                    success: function (data) {
                        let result = data.result;
                        if (result.ready) {
                            $.removeCookie('bnn-timer');
                            location.reload();
                        }
                    }
                });
            }

            checkScorings();
            // Обновление таймера каждую секунду
            let timerInterval = setInterval(function () {
                totalTime--;

                if (totalTime >= 0) {
                    $('#js-wait-scorings-timer').text(formatTime(totalTime));

                    if (totalTime >= 10) {
                        $.cookie('bnn-timer', totalTime);
                    }

                    if (totalTime > 0 && totalTime <= 160) {
                        // Каждые 10 секунд
                        if (totalTime % 10 === 0) {
                            checkScorings();
                        }
                    }
                } else {
                    // Останавливаем таймер, если время закончилось
                    clearInterval(timerInterval);
                    checkScorings(true);
                }

                updateProgressBar();
            }, 1000);
        })
    </script>
{/literal}

<input type="hidden" name="user_id" value="{$this->user->id}">

<section id="worksheet">
    <div>
        <div class="box">
            {if $is_short_flow}
                <hgroup>
                    <h1>Формируем окончательное предложение</h1>
                    <h5>Осталось <strong id="js-wait-scorings-timer">01:00</strong></h5>
                    {include file='display_stages.tpl' current=6 percent=80 total_step=6}
                </hgroup>
                {include
                file='partials/telegram_banner.tpl'
                margin='20px auto'
                source='nk'
                tg_banner_text='<h3>Ускорьте процесс и увеличьте шанс одобрения <br> Подпишитесь на наш Telegram канал </h3>'
                phone={{$phone}}
                }
            {else}
                <hgroup>
                    <h1 class="green">Проверяем данные</h1>
                    <h5>Это не займёт много времени.
                        <br>Осталось <strong id="js-wait-scorings-timer">03:00</strong></h5>
                </hgroup>
                {*include "loan_game.tpl"*}
            {/if}
        </div>
    </div>
</section>