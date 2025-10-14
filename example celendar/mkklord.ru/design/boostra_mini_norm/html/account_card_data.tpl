{* Страница заказа *}

{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
{/capture}

{literal}
	<style>
		.alert_card-add {
			background: #D2FBD0;
			color: #0C5F07;
			padding: 20px;
			border-radius: 10px;
		}
	</style>
{/literal}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				{if !empty($has_success_scorista)}
					<h5 class="alert_card-add">{$user->firstname}, ваш скор балл <b>{$scorista->scorista_ball}</b>. Вам одобрено <b>{$approve_amount}</b>.</h5>
				{/if}
				<h1>Добавьте карту, на которую<br>мы переведём деньги</h1>
				{if !$existTg && !$is_short_flow}
					{include
						file='partials/telegram_banner.tpl'
						margin='20px auto'
						source='nk'
						tg_banner_text='<h3>Вероятность одобрения может повыситься на 30% <br> Подпишись на наш Telegram канал </h3>'
						phone={{$phone}}
					}
				{/if}
			</hgroup>
			<!-- Перенести в файл, не быдлокодить сука -->
			<iframe id="add_card_frame" src="" style="display: none; width:100%; height:600px;border:0;" scrolling="no"></iframe>
            {*include file='display_stages.tpl' current=1*}

			{if $error}
				<h2 class="text-red animate-flashing">{$error}</h2>
			{/if}

            <div>
				<br/>
                {if $settings->b2p_enabled || $user->use_b2p}
                    <a href="#" class="button medium js-b2p-add-card" data-organization_id="{$organization_id}" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                {else}
                    {if $user->add_card}
                        <a href="{$user->add_card}" class="button medium" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                    {/if}
                {/if}
				<br/>
                <p class="security-text">Данные защищены сквозным шифрованием и передаются по безопасному соединению</p>

            </div>
		</div>
	</div>
</section>