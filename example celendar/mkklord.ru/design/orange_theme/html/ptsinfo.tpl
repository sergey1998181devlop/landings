{* Шаблон текстовой страницы *}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}

<section id="info">
	<div>
		<div class="box">
			<div>
				<!-- Заголовок страницы -->
				<h1 data-page="{$page->id}">{$page->header|escape}</h1>
				<!-- Тело страницы -->
				{$page->body}
			</div>
			{if $page->url == 'info'}
			<div id="demands">
				<h4>Требования к заемщику</h4>
				<ul>
					<li>
						<div class="icon passport"></div>
						<div class="about">Паспорт<br/> гражданина РФ</div>
					</li>
					<li>
						<div class="icon bcard"></div>
						<div class="about">Именная<br/> банковская карта</div>
					</li>
					<li>
						<div class="icon age"></div>
						<div class="about">Возраст<br/> от 21 года</div>
					</li>
					<li>
						<div class="icon number"></div>
						<div class="about">Активный<br/> номер мобильного</div>
					</li>
				</ul>
			</div>
			<div id="docs">
				<h4>Документы МФО</h4>
				<ul>
					<li><a href="/files/uploads/pravila-predostavleniya-zaimov.pdf" target="_blank">Правила предоставления займа</a></li>
					<li><a href="/files/uploads/individualnue_uslovia.pdf" target="_blank">Договор потребительского займа</a></li>
					<li><a href="/files/uploads/ogrn.jpg" target="_blank">Свидетельство ОГРН</a></li>
					<li><a href="/files/uploads/rekvisity.pdf" target="_blank">Реквизиты Организации</a></li>
					<li><a href="/files/uploads/pravila_obrabotk_personalnuh_dannuh.pdf" target="_blank">Правила обработки персональных данных</a></li>
				</ul>
			</div>
			<div id="contacts">
				<h4>Контакты</h4>
				<div>
					<div>
					ИНН/КПП {$config->org_inn}/ {$config->org_kpp}, ОГРН  {$config->org_ogrn}, ОКПО 21290962,
					</div>
					<div>
						р/с 40701810112300000784 в ПАО АКБ "АВАНГАРД", к/с 30101810000000000201,
						БИК ‎044525201
					</div>
					<div>
						Юридический адрес: {$config->org_legal_address}
					</div>
					<div>
						Фактический адрес: {$config->org_post_address}
					</div>
					<div>
						Почтовый адрес: {$config->org_post_address}
					</div>
					<div>
						Директор {$config->org_director} на основании Устава от {$config->org_date_charter} г.
					</div>
				</div>
				<div hidden>Электронная почта: <a href="mailto:{$config->org_email}">{$config->org_email}</a></div>
				<div hidden>Телефон: <a href="tel:{$config->org_phone|replace:' ':''}">{$config->org_phone}</a></div>
			</div>
			{/if}
		</div>
	</div>
</section>