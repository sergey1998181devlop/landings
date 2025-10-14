{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/account/login" scope=parent}

{$meta_title = "Добро пожаловать в личный кабинет {$config->org_name}" scope=parent}
{$meta_title2 = "{$config->org_name} личный кабинет официальный сайт компании" scope=parent}
{$meta_description = "Личный кабинет Бустра. Для входа понадобится телефон и пароль. Для входа нажмите «Войти»." scope=parent}
{$meta_keywords = "{$config->org_name} вход в личный кабинет" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}

{literal}
	<script src="https://unpkg.com/@vkid/sdk@1.1.0/dist-sdk/umd/index.js"></script>
	<script type="text/javascript" src="design/boostra_mini_norm/js/vk.js" defer></script>
{/literal}
<section id="login">
	<div>
		<div class="wrapper">
			<h2 id="login_form_title">Вход в личный кабинет</h2>
			<div>Используйте метод быстрой авторизации</div>
			<br>
				<div class="telegram-auth">
					<button class="telegram-button" onclick="window.open('https://t.me/boostra_helpbot_bot', '_blank')">
							<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30"
								 viewBox="0 0 48 48">
								<path fill="#29b6f6" d="M24 4A20 20 0 1 0 24 44A20 20 0 1 0 24 4Z"></path>
								<path fill="#fff"
									  d="M33.95,15l-3.746,19.126c0,0-0.161,0.874-1.245,0.874c-0.576,0-0.873-0.274-0.873-0.274l-8.114-6.733 l-3.97-2.001l-5.095-1.355c0,0-0.907-0.262-0.907-1.012c0-0.625,0.933-0.923,0.933-0.923l21.316-8.468 c-0.001-0.001,0.651-0.235,1.126-0.234C33.667,14,34,14.125,34,14.5C34,14.75,33.95,15,33.95,15z"></path>
								<path fill="#b0bec5"
									  d="M23,30.505l-3.426,3.374c0,0-0.149,0.115-0.348,0.12c-0.069,0.002-0.143-0.009-0.219-0.043 l0.964-5.965L23,30.505z"></path>
								<path fill="#cfd8dc"
									  d="M29.897,18.196c-0.169-0.22-0.481-0.26-0.701-0.093L16,26c0,0,2.106,5.892,2.427,6.912 c0.322,1.021,0.58,1.045,0.58,1.045l0.964-5.965l9.832-9.096C30.023,18.729,30.064,18.416,29.897,18.196z"></path>
							</svg>
							<p>Войти с телеграм </p>
					</button>
				</div>
			{if !$vk_disabled}
				<br>
				<div id="js-vkid-onetap"></div>
			{/if}
			{if $vk_error}
				<span class="message_error">{$vk_error}</span>
			{/if}
			<form method="post" id="send" {if $error}style="display: none;"{/if}>
				{if $error}
					<span class="error">
						{if $error == 'login_incorrect'}Неверные данные для входа
						{elseif $error == 'user_disabled'}Ваша учетная запись заблокирована
						{elseif $error == 'user_blocked'}Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.
						{else}{$error|escape}{/if}
                	</span>
				{/if}
				<div id="wrapper_fields">
					<label id="login_form_phone">
						<span id="login_form_description">Или авторизуйтесь с помощью<br/> мобильного телефона, который Вы указывали<br/> при получении займа</span>
						<div><input id="phoneInput" autocomplete="on" type="tel" name="phone" placeholder="Номер телефона" required="" {if $phone}value="{$phone}"{/if}/></div>
					</label>
				</div>

				{* recaptcha}
                    <div style="margin:20px auto 0 auto;display:inline-block">
                        <div id="recaptcha_register"></div>
                    </div>
                {*}

				{*<div>Пожалуйста, выберите ваш любимый мессенджер <br />и мы пришлем в него код</div>*}
				<div class="btn-login-group">
					{*<a href="javascript:void(0);" data-messenger="whatsapp" target="_blank" class="js-login-btn btn-login-wa"></a>*}
					{*<a href="javascript:void(0);" data-messenger="viber" class="js-login-btn btn-login-vi"></a>*}
					{*}
                    <a href="javascript:void(0);"  data-messenger="telegram"class="js-login-btn btn-login-tg"></a>
                    {*}
				</div>

				<button class="big new-button">Войти</button>

				<div id="login_form_footer"></div>
			</form>

			<br />
			{*<button id="gosuslugi" type="button"></button>*}
			<form method="post" id="check" {if $error}style="display: block;"{/if}>
				<input type="hidden" name="page_action" value="{$page_action}" />
				<label>
					<input type="hidden" name="login" value="1" />
					{if $error != 'user_blocked'}
						<span style="display:block;" id="check_title"></span>
						<div>
							<input type="text" name="key" placeholder="Код" required="" />
							<input type="hidden" name="real_phone" {if $phone}value="{$phone}"{/if}/>
						</div>
					{/if}
					{if $error}
						<div class="message_error">
							{if $error == 'login_incorrect'}Неверный код
							{elseif $error == 'user_disabled'}Ваш аккаунт был удален.
							{elseif $error == 'user_blocked'}Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.
							{else}{$error}{/if}
						</div>
					{/if}
				</label>
				{if $error != 'user_blocked'}
					<input type="submit" name="login" class="big button" value="Отправить" {if !$is_developer && !$is_admin}style="display:none"{/if} />
					<br/><br/>
					<div class="repeat_sms" style="margin-left:0">
						{*}<a href="#" class="new_sms">Отправить код еще раз</a>{*}
					</div>
					<script>
						var viberBotName = '{$settings->config->viberBotName}';
						var tlgBotName = '{$settings->config->tlgBotName}';
					</script>
					<div id="loginMessangers" {if !$error}style="display: none;"{/if}>
						<div id="codeInSms">
							<a id="loginBySms" href="javascript:void(0);" onclick="loginMessangers('sms');" class="button ">Отправить код через смс</a>
						</div>
						{*<div>Пожалуйста, выберите ваш любимый мессенджер <br />и мы пришлем в него код</div>
                        <div class="btn-login-group">
                            <a href="javascript:void(0);" onclick="loginMessangers('wa');" class="js-login-btn btn-login-wa"></a>
                            <a href="javascript:void(0);" onclick="loginMessangers('vi');" class="js-login-btn btn-login-vi"></a>
                            <a href="javascript:void(0);" onclick="loginMessangers('tg');" class="js-login-btn btn-login-tg"></a>
                        </div>*}
					</div>

				{/if}
			</form>
			<div id="smart-captcha-loan-container" style="display: none;" class="smart-captcha" data-sitekey="{$config->smart_captcha_client_key}"></div>

			<br>
			<div id="apps">
				<a href="https://redirect.appmetrica.yandex.com/serve/749596424009746204" target="_blank">
					<img src="/design/boostra_mini_norm/img/apps/175.png" style="width:90px;" />
				</a>
				&nbsp;
				<a href="https://redirect.appmetrica.yandex.com/serve/749647249176340067" target="_blank">
					<img src="/design/boostra_mini_norm/img/apps/176.png" style="width:90px;" />
				</a>
				&nbsp;
				<a href="https://redirect.appmetrica.yandex.com/serve/461366054585709806" target="_blank">
					<img src="/design/boostra_mini_norm/img/apps/177.png" style="width:90px;" />
				</a>
			</div>
		</div>
		{if isset($smarty.get.tid)}
			<a href="/ajax/auth/" id="auth-button-tinkoff" class="auth-button-tinkoff">Войти с Tinkoff ID</a>
			<input name="huid" type="hidden" value="{$authUrl}" />
		{/if}


	</div>
</section>
<script>
	$(document).ready(function() {
		$('#auth-button-tinkoff').on('click', function(e) {
			e.preventDefault();
			window.location.href = $(this).attr('href');
		});
	});
</script>
<style>
	.auth-button-tinkoff {
		display: block;
		/*margin-top: 20px;*/
		margin: 20px auto;
		max-width: 360px;
		background-color: #FFDD2D;
		color: black;
		text-decoration: none;
		padding: 10px 15px;
		border-radius: 5px;
		text-align: center;
		font-weight: bold;
	}

	button.big.new-button {
		background-color: #0077FF;
		color: #fff;
		transition: background-color 0.2s ease;
	}

	button.big.new-button:hover {
		background-color: #0071F2;
	}

	button.big.new-button:active {
		background-color: #0069E1;
	}

	.telegram-auth {
		text-align: center;
	}

	.telegram-button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background-color: #0077FF;
		color: white;
		border-radius: 8px;
		padding: 10px 15px;
		cursor: pointer;
		transition: background-color 0.2s ease;
		max-width: 360px;
		width: 100%;
	}

	.telegram-button:hover {
		background-color: #0071F2;
	}

	.telegram-button:active {
		background-color: #0069E1;
	}

	#apps {
		display: flex;
		gap: 12px;
		align-items: center;
	}
</style>
