{* Страница заказа *}

{$meta_title = "Заявка на займ | Boostra" scope=parent}
{$add_order_css_js = true scope=parent}
<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Для получения займа заполните анкету</h1>
				<h5>Она короткая. Мы не будем никому звонить.</h5>
			</hgroup>
			<div class="slider-box">
				<div class="progress">
					<span class="irs js-irs-0 irs-disabled">
						<span class="irs">
							<span class="irs-line" tabindex="-1">
								<span class="irs-line-mid"></span>
							</span>
							<span class="irs-min">начало анкеты</span>
							<span class="irs-max">выдача займа</span>
							<span class="irs-single" style="left: 4.57369%;">личные данные</span>
						</span>
						<span class="irs-grid"></span>
						<span class="irs-bar" style="width: 8.5%;"></span>
						<span class="irs-bar-edge"></span>
						<span class="irs-slider single" style="left: 18.5%;"></span>
						<span class="irs-disable-mask"></span>
					</span>
				</div>
			</div>
			<form method="post" id="neworder" action="neworder">
				<div id="steps">
					<fieldset>

						<label>
							<input type="tel" name="phone" placeholder="Телефон" required=""/>
						</label>

                        <label class="medium left">
							<div class="checkbox">
								<input type="checkbox" value="1" id="accept_check" name="accept" />
								<span></span>
							</div> Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                            <span class="error">Необходимо согласиться с условиями</span>
						</label>

						<div class="next">
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<label>
							<input type="text" name="lastname" id="lastname" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-lastname"></small>
							<span class="floating-label">Фамилия</span>
						</label>
						<label>
							<input type="text" name="firstname" id="firstname" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-firstname"></small>
							<span class="floating-label">Имя</span>
						</label>
						<label>
							<input type="text" name="patronymic" id="patronymic" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-patronymic"></small>
							<span class="floating-label">Отчество</span>
						</label>
						<label>
							<input type="text" name="birthday" placeholder="Дата рождения" required=""/>
						</label>
						<div class="next">
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<label>
							<input type="text" name="passportCode" placeholder="Серия и номер паспорта" required=""/>
						</label>
						<label>
							<input type="text" name="passportDate" placeholder="Дата выдачи" required=""/>
						</label>
						<label>
							<input type="text" name="subdivisionCode" placeholder="Код подразделения" required=""/>
						</label>
						<label class="big">
							<input type="text" name="passportWho" placeholder="Кем выдан" required=""/>
						</label>
						<div class="next">
							<a href="#prev" class="prev">Назад</a>
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<div class="register">
							<span class="title">Адрес прописки</span>
							<label>
								<input type="text" name="Regregion" placeholder="Область" required="" rel="region" aria-required="true"/>
							</label>
							<label>
								<input type="text" name="Regcity" placeholder="Город" required="" rel="city" aria-required="true"/>
							</label>
							<label>
								<input type="text" name="Regstreet" placeholder="Улица" rel="street"/>
							</label>
							<label>
								<input type="text" name="Reghousing" placeholder="Дом" required="" rel="house" aria-required="true"/>
							</label>
							<label>
								<input type="text" name="Regbuilding" placeholder="Строение" class="adding sup" rel="building" aria-required="true"/>
							</label>
							<label>
								<input type="text" name="Regroom" placeholder="Квартира" class="adding sup" rel="flat" aria-required="true"/>
							</label>
							<label class="big left">
								<div class="checkbox">
									<input type="checkbox" value="None" id="equal" name="equal" />
									<span></span>
								</div> Совпадает с адресом проживания
							</label>
						</div>
						<div class="living">
							<span class="title">Адрес проживания</span>
							<label>
								<input type="text" name="Faktregion" placeholder="Область" required="" rel="region"/>
							</label>
							<label>
								<input type="text" name="Faktcity" placeholder="Город" required="" rel="city"/>
							</label>
							<label>
								<input type="text" name="Faktstreet" placeholder="Улица" rel="street"/>
							</label>
							<label>
								<input type="text" name="Fakthousing" placeholder="Дом" required="" rel="house"/>
							</label>
							<label>
								<input type="text" name="Faktbuilding" placeholder="Строение" class="adding sup" rel="building"/>
							</label>
							<label>
								<input type="text" name="Faktroom" placeholder="Квартира" class="adding sup" rel="flat"/>
							</label>
							<input type="hidden" name="prop_okato" id="prop_okato" value="">
							<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
							<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
							<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">
							<input type="hidden" name="prog_okato" id="prog_okato" value="">
							<input type="hidden" name="prog_city_type" id="prog_city_type" value="">
							<input type="hidden" name="prog_street_type_long" id="prog_street_type_long" value="">
							<input type="hidden" name="prog_street_type_short" id="prog_street_type_short" value="">
						</div>
						<label class="big left">
							<div class="checkbox">
								<input type="checkbox" value="None" id="citizen" name="citizen]\" required="" />
								<span></span>
							</div> Являюсь гражданином РФ, даю согласие на <a style="text-decoration: underline;" href="/files/uploads/Положение о порядке сбора, обработки и хранения персональных данных.pdf" target="_blank">обработку и хранение персональных данных</a>
						</label>
						<label class="big left">
							<div class="checkbox">
								<input type="checkbox" value="None" id="conditions" name="conditions" required="" />
								<span></span>
							</div> С <a style="text-decoration: underline;" href="/files/uploads/individualnue_uslovia.pdf" target="_blank">условиями предоставления микрозаймов</a> ознакомлен, не действую в интересах третьих лиц
						</label>
						<div class="next">
							<input name="check_human" type="hidden" value=""/>
							<input name="check_sms" type="hidden" value=""/>
							<button class="button big" id="doit" type="submit" name="neworder" disabled>Готово</button>
							<a href="#prev" class="prev">Назад</a>
						</div>
					</fieldset>
					<fieldset>
						<div class="result">
							<h3>Все прошло отлично!</h3>
							<p>В течение 5 минут мы рассмотрим Вашу&nbsp;заявку.<br>
							Ответ будет сообщен по СМС, или звонком.</p>

							<a href="user" class="button big">Кабинет заемщика</a>

						</div>
						<div class="pixel_metric">
						{if $pixel}
							{$pixel}
						{/if}
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


    </div>

</div>