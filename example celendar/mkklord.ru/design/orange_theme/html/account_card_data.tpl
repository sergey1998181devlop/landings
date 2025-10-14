{* Страница заказа *}

{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Регистрация банковской карты</h1>
				<h5>Добавьте дебетовую (обычную) действующую карту, на которую мы сможем зачислить вам деньги.</h5>
			</hgroup>
			
            {*include file='display_stages.tpl' current=1*}
            
            <div>
				<br/>
                {if $settings->b2p_enabled || $user->use_b2p}
                    <a href="#" class="button medium js-b2p-add-card" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                {else}
                    {if $user->add_card}
                        <a href="{$user->add_card}" class="button medium" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                    {/if}
                {/if}
				<br/>
                <p>Данные защищены сквозным шифрованием и передаются по безопасному соединению</p>
                
            </div>
		</div>
	</div>
</section>