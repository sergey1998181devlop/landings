{* Страница заказа *}

{$meta_title = "Заявка на займ | Boostra" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.00" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.75" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/loan.app.js?v=1.81" type="text/javascript"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Для получения займа заполните анкету</h1>
				<h5>Несколько простых шагов и деньги у Вас на карте.</h5>
			</hgroup>
			
            <div class="stages">
                <ul>
                    <li class="current"><span>Контактная информация</span></li>
                    <li><span>Паспортные данные</span></li>
                    <li><span>Дополнительная информация</span></li>
                    <li><span>Идентификация</span></li>
                    <li><span>Получение денег</span></li>
                </ul>
            </div>
            
			<form method="post" id="loan_form" action="neworder" class=""> 
				<div id="steps">
					<fieldset style="display:block">
                        
                        <div class="step1">
                            <input type="hidden" name="amount" value="{$amount|escape}" />
                            <input type="hidden" name="period" value="{$period|escape}" />

                            <input type="hidden" name="service_recurent" value="1" />
                            <input type="hidden" name="service_sms" value="1" />
                            <input type="hidden" name="service_insurance" value="1" />
                            <input type="hidden" name="service_reason" value="0" />

                            <div class="clearfix">
                                <label class="{if $error=='empty_lastname'}error{/if}">
        							<input type="text" class="js-cirylic" name="lastname" id="lastname" placeholder="" value="{if $is_developer}Тестовый{else}{$lastname|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-lastname">{if $error=='empty_lastname'}Укажите Вашу фамилию{/if}</small>
        							<span class="floating-label">Фамилия</span>
        						</label>
        						<label class="{if $error=='empty_firstname'}error{/if}">
        							<input type="text" class="js-cirylic" name="firstname" id="firstname" placeholder="" value="{if $is_developer}Тест{else}{$firstname|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-firstname">{if $error=='empty_firstname'}Укажите Ваше имя{/if}</small>
        							<span class="floating-label">Имя</span>
        						</label>
        						<label class="{if $error=='empty_patronymic'}error{/if}">
        							<input type="text" class="js-cirylic" name="patronymic" id="patronymic" placeholder="" value="{if $is_developer}Тестович{else}{$patronymic|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-patronymic">{if $error=='empty_patronymic'}Укажите Ваше отчество{/if}</small>
        							<span class="floating-label">Отчество</span>
        						</label>
                            </div>
                            
                            <div class="clearfix">
                                
                                <label class="js-phone-block {if $error=='empty_phone'}error{/if}">
        							<input class="js-input-phone ym-record-keys" type="tel" name="phone" id="phone" placeholder="" value="{$phone|escape}" required="" aria-required="true">
        							<small class="err error" id="err-phone">{if $error=='empty_phone'}Укажите корректный телефон{/if}</small>
        							<span class="floating-label">Мобильный телефон</span>
        						</label>
                                
        						<label class="{if $error=='empty_email'}error{/if}">
        							<input class="js-input-email" type="text" name="email" id="email" placeholder="" data-email="{$email}" value="{if $is_developer}admin@test.ru{else}{$email}{/if}" >
        							<small class="err error" id="err-email">{if $error=='empty_email'}Укажите электронную почту{/if}</small>
        							<span class="floating-label">Электронная почта</span>
                                </label>
                                
                                <label class="{if $error=='empty_birth'}error{/if}">
        							<input type="text" name="birthday" value="{$birth}" placeholder="" required="" value="{if $is_developer}01.01.2000{/if}"/>
                                    <span class="floating-label">Дата рождения</span>
                                    {if $error=='empty_birth_place'}<span class="error">Укажите дата рождения</span>{/if}
        						</label>
    
                            </div>
                            <label class="js-info-block big" style="display:none"></label>
    						<div class="next">
    							<button class="button big js-send-code" type="button">Далее</button>
    						</div>
                        
                        </div>
                        <div class="step2" style="display:none">
                            
                            <input type="hidden" id="phone_checked" value="0" />
                            
                            <div class="clearfix">
                                
                                <label class="js-info-block medium" style="display:none"></label>
                                
                                <label class="big js-send-text">На Ваш телефон <span class="js-phone-number"></span> отправлено СМС-сообщение с кодом.</label>
    
                                <label class="js-code-block {if $error=='error_code'}error{/if}" >
        							<inpu autofocus autocomplete="one-time-code" class="js-input-code" type="text" name="code" id="code" placeholder="" value="" required="" aria-required="true">
        							<small class="err error" id="err-code">{if $error=='error_code'}Код не верный{/if}</small>
        							<span class="floating-label">Код из СМС</span>
        						</label>
    
                                <label class="js-send-block">
                                    <div class="repeat_sms js-send-repeat"></div>
                                    <button class="button small js-send-again" type="button" style="display:none">Отправить код еще раз</button>
                                </label>
                            </div>
                            
                            <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" style="display:">
    							<div class="checkbox">
    								<input class="js-input-accept" type="checkbox" value="1" id="accept_check" name="accept" {if $accept}checked="true"{/if} />
    								<span></span>
    							</div> Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                                <span class="error">Необходимо согласиться с условиями</span>
    						</label>
    
    						<div class="next js-submit-block" style="display:">
    							<button class="button big" type="submit">Далее</button>
    						</div>
                        </div>
					</fieldset>
					
				</div>
			</form>
		</div>
	</div>
</section>

<div class="hidden">
    
    <div id="accept" class="white-popup mfp-hide">
        <p>
            Я не являюсь должностным лицом, супругом или родственником должностного лица, 
            указанным в ст. 7.3 Федерального закона №115-ФЗ от 07.08.2001г.
        </p>
        <p>
            Я не буду действовать к выгоде другого лица при проведении сделок и иных операций
        </p>
        <p>
            У меня отсутствует бенефициарный владелец - стороннее физическое лицо, а также представитель отсутствует
        </p>
        <p>
            Настоящим я подтверждаю свое ознакомление и согласие с ниже представленными документами, 
            а также подтверждаю их подписание с использованием аналога собственноручной подписи:
        </p>
        <ul style="padding-left:10px;">
            {foreach $docs as $doc}
            {if $doc->in_register}
            <li>
                <a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">{$doc->name|escape}</a>
            </li>
            {/if}
            {/foreach}
        </ul>

        <div>
            <label>
                <div class="checkbox service_checkbox">
        			<input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent" checked="true" />
        			<span></span>
        		</div> 
                Согласен на подключение услуги реккурентных платежей, предоставляющейся в соответствии с 
                <a href="{$config->root_url}/files/docs/soglashenie-o-regulyarnyh-rekurrentnyh-platezhah-mkk-ooo-bustra.pdf" target="_blank">"Соглашением о регулярных (рекуррентных) платежах МКК ООО «{$config->org_name}»"</a>
            </label>
        </div>
        
        <div>
            <label>
                <div class="checkbox service_checkbox">
        			<input class="js-service-sms" type="checkbox" value="1" id="service_sms_check" name="service_sms" checked="true" />
        			<span></span>
        		</div> 
                Cогласен на подключение услуги смс-информирование, предоставляющейся в соответствии с 
                <a href="{$config->root_url}/files/docs/polozhenie-o-dopolnitelnyh-finansovyh-uslugah--mkk--ooo-bustra-sms-informirovanie-.pdf" target="_blank">"Положением о дополнительных финансовых услугах МКК ООО «{$config->org_name}» «СМС-ИНФОРМИРОВАНИЕ»"</a>
            </label>
        </div>        
        
        <div>
            <label>
                <div class="checkbox service_checkbox">
        			<input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance" checked="true" />
        			<span></span>
        		</div> 
                    Заявляю свое желание заключить договор страхования  соответственно 
                    <a href="{$config->root_url}/files/docs/pravila--195-kombinirovannogo-strahovaniya-ot-neschastnyh-sluchaev-i-boleznej.pdf" target="_blank">"Правилам №195 комбинированного страхования от несчатных случаев и болезней"</a>,
                    страховая премия 10% от суммы полученного займа, срок страхования 30 дней, страховая сумма 200% от суммы полученного займа 
            </label>
        </div>
        
        {*}
        <div>
            <label>
                <div class="checkbox service_checkbox">
        			<input class="js-service-reason" type="checkbox" value="1" id="service_reason_check" name="service_reason" checked="true" />
        			<span></span>
        		</div> В случае отказа по заявке, я хочу получить описание причины отказа в соответствии с <a href="https://www.boostra.ru/files/docs/polozhenie-o-dopolnitelnyh-finansovyh-uslugah-mkk-ooo-bustra-stop-list.pdf" target="_blank">Положением</a>
            </label>
        </div>
        {*}
        
        <button title="Close (Esc)" type="button" class="mfp-close">OK</button>
    </div>
    
</div>




