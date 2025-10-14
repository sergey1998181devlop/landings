{* Страница заказа *}

{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.00" type="text/javascript"></script>
{*	<link rel="stylesheet" type="text/css" href="design/boostra_mini_norm/css/style.css?v=1.00"/>*}
	{*<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>*}

    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	{*<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>*}
	<script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
	<link rel="stylesheet" href="/js/autocomplete/styles.css" />

	<script src="design/{$settings->theme}/js/personal_data.app.js?v=1.74" type="text/javascript"></script>
{if $settings->addresses_is_dadata}{* подсказки через dadata *}
	<script src="design/{$settings->theme}/js/dadata_init.js?v=1.10" type="text/javascript"></script>
{else}{* подсказки через kladr *}
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>
	<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.62" type="text/javascript"></script>
{/if}

{/capture}

<style>
	.floating-label.required::after {
		content: '*';
		color: red;
		margin-left: 4px;
	}

	.floating-label-default {
		font-size: 1rem !important;
	}

	.select {
		position: relative;
		display: inline-block;
		width: 100%;
	}

	.select select {
		width: 100%;
		padding-right: 40px;
		color: #000;
		cursor: pointer;
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
	}

	.select::after {
		content: '▼';
		position: absolute;
		top: 50%;
		right: 15px;
		transform: translateY(-50%);
		pointer-events: none;
		color: #555;
		font-size: 12px;
	}

</style>


<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Адрес</h1>
				{if !$existTg}
					{include
						file='partials/telegram_banner.tpl'
						margin='20px auto'
						source='nk'
						tg_banner_text='<h3>Вероятность одобрения может повыситься на 30% <br> Подпишись на наш Telegram канал </h3>'
						phone={{$phone}}
					}
				{/if}
				<h5>Заполните адрес регистрации и получите решение.</h5>
			</hgroup>


			{include file='display_stages.tpl' current=4 percent=63 total_step=4}

			<form method="post" id="personal_data" class="js-send-feedback" data-target="etap-propiska">
				<div id="steps">

					{if $error == 'allready_exists'}
                    <div class="alert alert-danger">
                        Клиент с такими персональными данными уже зарегистрирован.
                        <br />
                        Просим связаться с нами по номеру {$config->org_phone}.
                    </div>

                    {else}

					<fieldset style="display: block;;">

                        <input type="hidden" value="address_data" name="stage" />

						<div class="register">

                            <span class="title">Адрес регистрации</span>

                            <label class="{if $error=='empty_Regregion'}error{/if} region--label" id="regregion-label">
                                <div class="select">
									<select id="Regregion" name="Regregion" required>
										<option value="" disabled selected hidden>Выберите регион</option>
										{foreach $regions as $region}
											{assign var="region_name_upper" value=$region->name|trim|mb_strtoupper}
											<option value="{$region->short_name|escape}"
												{if !empty($registration_region)}
													{assign var="registration_region_upper" value=$registration_region|trim|mb_strtoupper}
													{if strpos($region_name_upper, $registration_region_upper) !== false} selected="" {/if}
												{elseif !empty($user->Regregion)}
													{assign var="user_regregion_upper" value=$user->Regregion|trim|mb_strtoupper}
													{if strpos($region_name_upper, $user_regregion_upper) !== false} selected="" {/if}
												{/if}>
												{$region->name|escape}
											</option>
										{/foreach}
									</select>
								</div>
                                <span class="floating-label floating-label-default required ">Область/Регион/Край</span>
                                {if $error=='empty_Regregion'}<span class="error">Укажите Регион в которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regcity}readonly{/if} {if $error=='empty_Regcity'}error{/if}" id="regcity-label">
								<input type="text" name="Regcity" value="{if $registration_city}{$registration_city|escape}{else}{$user->Regcity|escape}{/if}" placeholder="" required="required" rel="city" aria-required="true" data-selected = 'false'/>
                                <span class="floating-label required">Населенный пункт</span>
                                {if $error=='empty_Regcity'}<span class="error">Укажите Населенный пункт в котором Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regstreet}readonly{/if} {if $error=='empty_Regstreet'}error{/if}" id="regstreet-label">
								<input type="text" name="Regstreet" value="{if $registration_street}{$registration_street|escape}{else}{$user->Regstreet|escape}{/if}" placeholder="" rel="street" data-selected = 'false'/>
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Regstreet'}<span class="error">Укажите улицу на которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Reghousing}readonly{/if} {if $error=='empty_Reghousing'}error{/if}" id="reghousing-label">
								<input type="text" name="Reghousing" value="{if $registration_house}{$registration_house|escape}{else}{$user->Reghousing|escape}{/if}" placeholder="" rel="house" aria-required="true" data-selected = 'false'/>
                                <span class="floating-label">Номер дома</span>
                                {if $error=='empty_Reghousing'}<span class="error">Укажите номер дома в котором Вы прописаны</span>{/if}
								<small>если есть</small>
							</label>

							<label class="{if $user->Regbuilding}readonly{/if} ">
								<input type="text" name="Regbuilding" value="{if $registration_building}{$registration_building|escape}{else}{$user->Regbuilding|escape}{/if}" placeholder="" class="adding sup" rel="building" aria-required="true"/>
							    <span class="floating-label">Строение</span>
								<small>если есть</small>
                            </label>

							<label class="{if $user->Regroom}readonly{/if} ">
								<input type="text" name="Regroom" value="{if $registration_apartment}{$registration_apartment|escape}{else}{$user->Regroom|escape}{/if}" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
							    <span class="floating-label">Номер квартиры</span>
								<small>если есть</small>
                            </label>

							<label class="big left">
								<div class="checkbox">
									<input type="checkbox" value="1" id="equal" name="equal" {if $equal}checked="true"{/if} />
									<span></span>
								</div> Адрес регистрации совпадает с адресом проживания
							</label>

						</div>

						<div class="living" id="living_block" {if $equal}style="display:none"{/if}>

                            <span class="title">Адрес проживания</span>

                            <label class="{if $error=='empty_Faktregion'}error{/if} region--label" id="faktregion-label">
                                <div class="select">
									<select id="Faktregion" name="Faktregion" required>
										<option value="" disabled selected hidden>Выберите регион</option>
										{foreach $regions as $region}
											{assign var="region_name_upper" value=$region->name|trim|mb_strtoupper}
											<option value="{$region->short_name|escape}"
												{if !empty($factual_region)}
													{assign var="factual_region_upper" value=$factual_region|trim|mb_strtoupper}
													{if strpos($region_name_upper, $factual_region_upper) !== false} selected="" {/if}
												{elseif !empty($user->Faktregion)}
													{assign var="user_faktregion_upper" value=$user->Faktregion|trim|mb_strtoupper}
													{if strpos($region_name_upper, $user_faktregion_upper) !== false} selected="" {/if}
												{/if}>
												{$region->name|escape}
											</option>
										{/foreach}
									</select>
                                </div>
                                <span class="floating-label floating-label-default required">Область/Регион/Край</span>
                                {if $error=='empty_Faktregion'}<span class="error">Укажите Регион фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Faktcity}readonly{/if} {if $error=='empty_Faktcity'}error{/if}"  id="faktcity-label">
								<input type="text" name="Faktcity" value="{if $residence_city}{$residence_city|escape}{else}{$user->Faktcity|escape}{/if}" placeholder="" required="" rel="city" data-selected = 'false'/>
                                <span class="floating-label required">Населенный пункт</span>
                                {if $error=='empty_Faktcity'}<span class="error">Укажите Населенный пункт фактического проживания</span>{/if}
							</label>

                            <label class="{if $user->Faktstreet}readonly{/if} {if $error=='empty_Faktstreet'}error{/if}" id="faktstreet-label">
								<input type="text" name="Faktstreet" value="{if $residence_street}{$residence_street|escape}{else}{$user->Faktstreet|escape}{/if}" placeholder="" rel="street" data-selected = 'false'/>
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Faktstreet'}<span class="error">Укажите улицу фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Fakthousing}readonly{/if} {if $error=='empty_Fakthousing'}error{/if}" id="fakthousing-label">
								<input type="text" name="Fakthousing" value="{if $residence_house}{$residence_house|escape}{else}{$user->Fakthousing|escape}{/if}" placeholder="" rel="house" data-selected = 'false'/>
                                <span class="floating-label">Номер дома</span>
                                {if $error=='empty_Fakthousing'}<span class="error">Укажите номер дома фактического проживания</span>{/if}
								<small>если есть</small>
							</label>

							<label class="{if $user->Faktbuilding}readonly{/if}">
								<input type="text" name="Faktbuilding" value="{if $residence_building}{$residence_building|escape}{else}{$user->Faktbuilding|escape}{/if}" placeholder="" class="adding sup" rel="building"/>
							    <span class="floating-label">Строение</span>
								<small>если есть</small>
                            </label>

							<label class="{if $user->Faktroom}readonly{/if}">
								<input type="text" name="Faktroom" value="{if $residence_apartment}{$residence_apartment|escape}{else}{$user->Faktroom|escape}{/if}" placeholder="" class="adding sup" rel="flat"/>
							    <span class="floating-label">Номер квартиры</span>
								<small>если есть</small>
                            </label>

							<input type="hidden" name="Regindex" id="prop_zip" value="{if $registration_zipCode}{$registration_zipCode|escape}{else}{$user->Regindex|escape}{/if}">

							<input type="hidden" name="Regregion_shorttype" id="regregion_shorttype" value="{$user->Regregion_shorttype}">
							<input type="hidden" name="Regcity_shorttype" id="regcity_shorttype" value="{$user->Regcity_shorttype}">
							<input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype" value="{$user->Regstreet_shorttype}">

							<input type="hidden" name="prop_okato" id="prop_okato" value="">
							<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
							<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
							<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">

                            <input type="hidden" name="Faktindex" id="prog_zip" value="{if $residence_zipCode}{$residence_zipCode|escape}{else}{$user->Faktindex|escape}{/if}">

							<input type="hidden" name="Faktregion_shorttype" id="faktregion_shorttype" value="{$user->Faktregion_shorttype}">
							<input type="hidden" name="Faktcity_shorttype" id="faktcity_shorttype" value="{$user->Faktcity_shorttype}">
							<input type="hidden" name="Faktstreet_shorttype" id="faktstreet_shorttype" value="{$user->Faktstreet_shorttype}">

                            <input type="hidden" name="prog_okato" id="prog_okato" value="">
							<input type="hidden" name="prog_city_type" id="prog_city_type" value="">
							<input type="hidden" name="prog_street_type_long" id="prog_street_type_long" value="">
							<input type="hidden" name="prog_street_type_short" id="prog_street_type_short" value="">

						</div>

                        <div class="next">
							<button class="button big" id="doit" type="submit" name="neworder">Получить решение</button>
						</div>
					</fieldset>
                    {/if}
				</div>
			</form>

			{include 'modals/inactivity_modal.tpl'}
		</div>
	</div>
</section>