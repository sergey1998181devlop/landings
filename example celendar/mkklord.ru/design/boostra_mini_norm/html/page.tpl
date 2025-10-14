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

            {if $page->url == 'credit_holidays'}
			{/if}

            {if $page->url == 'covid19'}
            <div id="docs">

            <div id="covid">
                <p>
                    Уважаемый клиент, сообщаем вам, что в соответствии с "Федеральным законом от 3 апреля 2020 г. N 106-ФЗ
                    "О внесении изменений в Федеральный закон "О Центральном банке Российской Федерации (Банке России)" и отдельные законодательные акты Российской Федерации в части особенностей изменения условий кредитного договора, договора займа", вы имеете право обратиться с заявлением на реструктуризацию займа или с запросом о предоставлении кредитных каникул
                </p>
                <ul>
                    {foreach $docs as $doc}
                    {if $doc->id == 32 || $doc->id == 33 || $doc->id == 34}
                    <li><a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">{$doc->name|escape}</a></li>
                    {/if}
                    {/foreach}
                </ul>
            </div>

			</div>
            {/if}

            {if $page->url == 'info'}

				<div class="partners_docs">
					<div class="partner_title" data-modal="modal1">
						<h2>{$config->org_name}</h2>
					</div>
					<div class="partner_title" data-modal="modal2">
						<h2>Аквариус</h2>
					</div>
					<div class="partner_title" data-modal="modal3">
						<h2>ВипЗайм</h2>
					</div>
				</div>

				<div id="modal1" class="modal_partners">
					<div class="modal-content">
						<span class="close" data-modal="modal1">&times;</span>
						<h2 class="text-center">{$config->org_name}</h2>
						<p class="text-center">Документы {$config->org_name}.</p>
						<ul>
							<li><a target="_blank" href="/files/docs/finlab/bazovyj-standart-soversheniya-mikrofinansovoj-organizacziej-operaczij-na-finansovom-rynke.pdf">Базовый стандарт совершения микрофинансовой организацией операций на финансовом рынке</a></li>
							<li><a target="_blank" href="/files/docs/finlab/123-FZ.pdf">123 ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/finlab/230-FZ.pdf">230 ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Bazovyj-standart-po-upravleniyu-riskami-mikrofinansovyh-organizacij.pdf">Базовый стандарт по управлению рисками микрофинансовых организаций</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Vypiska-iz-gos-reestra.pdf">Выписка из гос.реестра</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Vypiska_iz_Protokola_Soveta_Soyuza_№13_23_ot_08_08_2023.pdf">Выписка из Протокола Совета Союза №13 23 от 08.08.2023.pdf</a></li>
							<li><a target="_blank" href="/files/docs/finlab/ZAYAVLENIE_o_predostavlenii_mikrozajma_FINLAB.docx">ЗАЯВЛЕНИЕ о предоставлении микрозайма ФИНЛАБ</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Informaciya-o-prave-potrebitelej-finansovyh-uslug-na-napravlenie-obrashcheniya-finansovomu-upolnomochennomu.pdf">Информация-о-праве-потребителей-финансовых-услуг-на-направление-обращения-финансовому-уполномоченному</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Mery-podderzhki-Zaemshchikov.pdf">Меры поддержки Заемщиков</a></li>
							<li><a target="_blank" href="/files/docs/finlab/O-mekhanizme-predostavleniya-Kreditnyh-Kanikul-2024.pdf">О механизме предоставления Кредитных Каникул 2024</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Obshchie-usloviya-OOO-MKK-FINLAB-ot-01.06.2024.docx">Общие условия ООО МКК ФИНЛАБ от 01.06.2024</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Oferta-ob-usloviyah-ispol'zovanie-servisa-processingovogo-centra.pdf">Оферта об условиях использование сервиса процессингового центра</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Informaciya-o-licah,-okazyvayushchih-znachitel'noe-vliyanie-OOO-MKK-FINLAB.docx">п. 2.3. Информация о лицах, оказывающих значительное влияние ООО МКК ФИНЛАБ</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Pamyatka-ob-usloviyah-predostavleniya-voennosluzhashchim-kreditnyh-kanikul.pdf">Памятка об условиях предоставления военнослужащим кредитных каникул</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Personal'nyj-sostav-EIO.docx">Персональный состав ЕИО</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Politika-bezopasnosti-platezhej-Best2Pay.pdf">Политика безопасности платежей Best2Pay</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Politika-konfidencial'nosti.docx">Политика конфиденциальности</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Polozhenie-o-privlechenii-denezhnyh-sredstv-fiz-lic-uchreditelej.docx">Положение о привлечении денежных средств физ лиц учредителей</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Polozhenie-o-privlechenii-denezhnyh-sredstv-fiz-lic-uchreditelej.pdf">Положение о привлечении денежных средств физ лиц учредителей</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Polozhenie-o-trebovaniyah-k-soderzhaniyu-obrashchenij-poluchatelej-finansovyh-uslug.docx">Положение о требованиях к содержанию обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Polozhenie-o-trebovaniyah-k-soderzhaniyu-obrashchenij-poluchatelej-finansovyh-uslug.pdf">Положение о требованиях к содержанию обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Polozhenie-po-rassmotreniyu-obrashchenij-poluchatelej-finansovyh-uslug.docx">Положение по рассмотрению обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Ponyatie-«mikrofinansovaya-organizaciya».pdf">Понятие «микрофинансовая организация»</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Poryadok_raboty_s_prosrochennoj_zadolzhennost'yu-01.06.2024.docx">Порядок_работы_с_просроченной_задолженностью 01.06.2024</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Pravila-obrabotki-i-zashchity personal'nyh-dannyh.docx">Правила обработки и защиты персональных данных</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Pravila-ocenki-vreda,-kotoryj-mozhet-byt'-prichinen-subektam-personal'nyh-dannyh-v-sluchae-narusheniya-trebovanij.docx">Правила оценки вреда, который может быть причинен субъектам персональных данных в случае нарушения требований.docx</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Pravila-predostavleniya-zajmov-01.06.2024-FINLAB.docx">Правила предоставления займов 01.06.2024 ФИНЛАБ</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Rekomendacii-dlya-zaemshchikov.docx">Рекомендации для заемщиков</a></li>
							<li><a target="_blank" href="/files/docs/finlab/Ustav.pdf">Устав</a></li>
							<li><a target="_blank" href="/files/docs/finlab/FINLAB-IU-DZ-redakciya-01.06.2024-(NUZHEN-QR-kod).docx">ФИНЛАБ ИУ ДЗ редакция 01.06.2024 (НУЖЕН QR-код)</a></li>
						</ul>
					</div>
				</div>

				<div id="modal2" class="modal_partners">
					<div class="modal-content">
						<span class="close" data-modal="modal2">&times;</span>
						<h2 class="text-center">Аквариус</h2>
						<p class="text-center">Документы Аквариус</p>
						<ul>
							<li><a target="_blank" href="/files/docs/akvarius/1-vypiska-iz-gosudarstvennogo-reestra-MFO-CBRF.pdf">Выписка из государственного реестра МФО ЦБ РФ</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/2-vypiska-iz-reestra-chlenov-SRO.pdf">Выписка из реестра членов СРО</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/3-svidetelstvo-INN.pdf">Свидетельство ИНН</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/4-ustav.pdf">Устав</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/5-politika-obrabotki-i-hraneniya-personalnyh-dannyh.pdf">Политика обработки и хранения персональных данных</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/6-soglashenie-ob-ispolzovanii-analoga-sobstvennoruchnoy-podpisi.pdf">Соглашение об использовании аналога собственноручной подписи</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/7-obshie-usloviya-dogovora-zayma.pdf">Общие условия договора займа</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/8-pravila-predostavleniya-zaymov.pdf">Правила предоставления займов</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/9-informaciya-dlya-poluchateley-finansovyh-uslug.pdf">Информация для получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/10-politika-konfidencialnosti.pdf">Политика конфиденциальности</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/11-poryadok-rassmotreniya-obrashcheniy-poluchateley-finansovyh-uslug.pdf">Порядок рассмотрения обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/12-bazovy-standart-zashchity-prav-i-interesov-poluchateley-finansovyh-uslug.pdf">Базовый стандарт защиты прав и интересов получателей
									финансовых
									услуг</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/13-bazovy-standart-po-upravleniyu-riskami-mikrofinansovyh-organizaciy.pdf">Базовый стандарт по управлению рисками микрофинансовых
									организаций</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/14-bazovy-standart-soversheniya-MFO-operaciy-na-finansovom-rynke.pdf">Базовый стандарт совершения МФО операций на финансовом рынке</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/15-zakon-RF-ot-07-02-1992-N2300-1-O-Zashchite-Prav-Potrebiteley.pdf">Закон РФ от 07.02.1992 № 2300-1 'О защите прав потребителей'</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/16-informacionnaya-broshyura-Banka-Rossii-ob-MFO.pdf">Информационная брошюра Банка России об МФО</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/17-informaciya-o-podache-obrashcheniya-v-adres-FU.pdf">Информация о подаче обращения в адрес ФУ</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/18-informaciya-o-riskah-dostupa-k-zashchishchaemoy-informacii.pdf">Информация о рисках доступа к защищаемой информации</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/19-oferta-ob-ispolzovanii-processnogo-centra-BEST2PAY.pdf">Оферта об использовании процессного центра BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/20-politika-bezopasnosti-platezhey-BEST2PAY.pdf">Политика безопасности платежей BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/21-pamyatka-Banka-Rossii-o-kreditnyh-kanikulah-dlya-uchastnikov-SVO.pdf">Памятка Банка России о кредитных каникулах для участников СВО</a>
							</li>
							<li><a target="_blank" href="/files/docs/akvarius/22-informaciya-o-kreditnyh-kanikulah-353-FZ.pdf">Информация о кредитных каникулах 353-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/23-informaciya-o-kreditnyh-kanikulah-377-FZ.pdf">Информация о кредитных каникулах 377-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/akvarius/24-perechen-tretih-lic-kotorym-peredayutsya-polzovatelskie-dannye.pdf">Перечень третьих лиц, которым передаются пользовательские данные</a>
							</li>
							<li><a target="_blank" href="/files/docs/akvarius/25-ssylki-na-stranicy-saytov-ispolzuemyh-dlya-privlecheniya-klientov.pdf">Ссылки на страницы сайтов, используемых для привлечения клиентов</a>
							<li><a target="_blank" href="/files/docs/akvarius/26-uslovia-i-porjadok-predostavlenia-zaimov.pdf">Условия и порядок предоставления займов</a>
							</li>
						</ul>
					</div>
				</div>

				<div id="modal3" class="modal_partners">
					<div class="modal-content">
						<span class="close" data-modal="modal3">&times;</span>
						<h2 class="text-center">ВипЗайм</h2>
						<p class="text-center">Документы ВипЗайм.</p>
						<ul>
							<li><a target="_blank" href="/files/docs/viploan/Bazovyj-standart-zashchity-prav-i-interesov-fizicheskih-i-yuridicheskih-lic-poluchatelej-finansovyh-uslug.pdf">Базовый стандарт защиты прав и интересов физических и юридических лиц</a></li>
							<li><a target="_blank" href="/files/docs/viploan/123 ФЗ.pdf">123 ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/viploan/230 ФЗ.pdf">230 ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/viploan/bazovyj-standart-soversheniya-mikrofinansovoj-organizacziej-operaczij-na-finansovom-rynke.pdf">Базовый стандарт совершения микрофинансовой организацией на финансовом рынке</a></li>
							<li><a target="_blank" href="/files/docs/viploan/Bazovyj-standart-po-upravleniyu-riskami-mikrofinansovyh-organizacij.pdf">Базовый стандарт по управлению рисками микрофинансовых организаций</a></li>
							<li><a target="_blank" href="/files/docs/viploan/vipzai-m-iu-dz-redakciya-01-06-2024-nuzhen-qr-kod.docx">ВипЗайм ИУ ДЗ редакция 01.06.2024 (НУЖЕН QR-код)</a></li>
							<li><a target="_blank" href="/files/docs/viploan/vypiska-iz-gos-reestra.pdf">Выписка из гос реестра</a></li>
							<li><a target="_blank" href="/files/docs/viploan/vypiska-iz-protokol-soveta-soyuza-10-24-ot-23-05-2024-2.pdf">Выписка из Протокол Совета Союза №10 24 от 23.05.2024</a></li>
							<li><a target="_blank" href="/files/docs/viploan/zayavlenie-o-predostavlenii-mikrozai-ma-vipzai-m.docx">ЗАЯВЛЕНИЕ о предоставлении микрозайма ВипЗайм</a></li>
							<li><a target="_blank" href="/files/docs/viploan/informaciya-o-prave-potrebitelei-finansovykh-uslug-na-napravlenie-obrashcheniya-finansovomu-upolnomochennomu.pdf">Информация о праве потребителей финансовых услуг на направление обращения финансовому уполномоченному</a></li>
							<li><a target="_blank" href="/files/docs/viploan/mery-podderzhki-zaemshchikov.pdf">Меры поддержки Заемщиков</a></li>
							<li><a target="_blank" href="/files/docs/viploan/o-mekhanizme-predostavleniya-kreditnykh-kanikul-2024.pdf">О механизме предоставления Кредитных Каникул 2024</a></li>
							<li><a target="_blank" href="/files/docs/viploan/obshchie-usloviya-ooo-mkk-vipzai-m-ot-01-06-2024.docx">Общие условия ООО МКК ВипЗайм от 01.06.2024</a></li>
							<li><a target="_blank" href="/files/docs/viploan/oferta-ob-usloviyakh-ispolzovanie-servisa-processingovogo-centra.pdf">Оферта об условиях использование сервиса процессингового центра</a></li>
							<li><a target="_blank" href="/files/docs/viploan/p-2-3-informaciya-o-licakh-okazyvayushchikh-znachitelnoe-vliyanie-ooo-mkk-vipzai-m.docx">п. 2.3. Информация о лицах, оказывающих значительное влияние ООО МКК ВипЗайм</a></li>
							<li><a target="_blank" href="/files/docs/viploan/pamyatka-ob-usloviyakh-predostavleniya-voennosluzhashchim-kreditnykh-kanikul.pdf">Памятка об условиях предоставления военнослужащим кредитных каникул</a></li>
							<li><a target="_blank" href="/files/docs/viploan/personalnyi-sostav-eio.docx">Персональный состав ЕИО</a></li>
							<li><a target="_blank" href="/files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf">Политика безопасности платежей Best2Pay</a></li>
							<li><a target="_blank" href="/files/docs/viploan/politika-konfidencialnosti.docx">Политика конфиденциальности</a></li>
							<li><a target="_blank" href="/files/docs/viploan/polozhenie-o-privlechenii-denezhnykh-sredstv-fiz-lic-uchreditelei-.docx">Положение о привлечении денежных средств физ лиц учредителей.docx</a></li>
							<li><a target="_blank" href="/files/docs/viploan/polozhenie-o-trebovaniyakh-k-soderzhaniyu-obrashchenii-poluchatelei-finansovykh-uslug.docx">Положение о требованиях к содержанию обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/viploan/polozhenie-o-trebovaniyakh-k-soderzhaniyu-obrashchenii-poluchatelei-finansovykh-uslug.pdf">Положение о требованиях к содержанию обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/viploan/polozhenie-po-rassmotreniyu-obrashchenii-poluchatelei-finansovykh-uslug.docx">Положение по рассмотрению обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/viploan/ponyatie-mikrofinansovaya-organizaciya.pdf">Понятие «микрофинансовая организация»</a></li>
							<li><a target="_blank" href="/files/docs/viploan/poryadok-raboty-s-prosrochennoi-zadolzhennostyu-01-06-2024.docx">Порядок работы с просроченной задолженностью 01.06.2024</a></li>
							<li><a target="_blank" href="/files/docs/viploan/pravila-obrabotki-i-zashchity-personalnykh-dannykh.docx">Правила обработки и защиты персональных данных</a></li>
							<li><a target="_blank" href="/files/docs/viploan/pravila-predostavleniya-zai-mov-01-06-2024-vipzai-m.docx">Правила предоставления займов 01.06.2024 Випзайм</a></li>
							<li><a target="_blank" href="/files/docs/viploan/rekomendacii-dlya-zaemshchikov.docx">Рекомендации для заемщиков</a></li>
							<li><a target="_blank" href="/files/docs/viploan/ustav.pdf">Устав</a></li>
						</ul>
					</div>
				</div>

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
						<div class="about">Возраст<br/> от 18 лет</div>
					</li>
					<li>
						<div class="icon number"></div>
						<div class="about">Активный<br/> номер мобильного</div>
					</li>
				</ul>
			</div>
			{*}
            <div id="docs">
				<h4>ООО МКК "Аквариус"</h4>
				<ul>
					{foreach $docs as $doc}
                    {if $doc->in_info}
                    <li><a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}?v={$doc->version}" target="_blank">{$doc->name|escape}</a></li>
                    {/if}
                    {/foreach}
				</ul>
			</div>
            {*}

			<div id="contacts">
                {*}
				<h4>Контакты</h4>
				<div>
					<div>
						ИНН/ КПП организации: 9714011290/771401001<br/>
                        ОГРН 1237700365506<br/>
					</div>
					<div>
						р/с 40701810900000008895
						в АО «Тинькофф Банк» корсчет 30101810145250000974, БИК 044525974
					</div>
					<div>
						Юридический адрес: 125319, г. Москва., вн. тер. г. муниципальный округ Аэропорт, ул. Академика Ильюшина, д. 12, помещ. 2/1
					</div>
                    <div>
						Местонахождение постоянно действующего исполнительного органа: 125319, г. Москва., вн. тер. г. муниципальный округ Аэропорт, ул. Академика Ильюшина, д. 12, помещ. 2/1
                    </div>
                    <br />
					<div>
                        Режим работы:<br />
                        понедельник-пятница - с 9-00 до 18-00<br />
                        суббота-воскресенье - выходной
                    </div>
                    <br />
                    <div>
						Генеральный директор Поздняков Сергей Владимирович на основании Устава.
					</div>
				</div>
				<div>Телефон: <a href="tel:88005518881">8 (800) 551-88-81</a></div>
				<br />
				
                <div>
					<div style="display: flex; align-items: center;">
						<img src="design/{$settings->theme|escape}/img/qr_code_1.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
						Официальный сайт финансового уполномоченного в сети "Интернет". Финансовый уполномоченный осуществляет досудебное урегулирование споров между потребителями финансовых услуг и финансовыми организациями.
					</div>
					<br />
					<div>
						<div style="display: flex; align-items: center;">
							<img src="design/{$settings->theme|escape}/img/qr_code_2.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
							Сайт Федеральной службы судебных приставов в сети "Интернет", содержащий форму для подачи жалоб и обращений на нарушение прав и законных интересов физических лиц при осуществлении деятельности по возврату просроченной задолженности физических лиц, возникшей из денежных обязательств.
						</div>
					</div>

				</div>
                {*}

					<div>
						{include file="callBackForm.tpl"}
					</div>

		</div>
			{/if}
				{*}
                <br />
				<div>
					<div style="display: flex; align-items: center;">
						<img src="design/{$settings->theme|escape}/img/qr_code_1.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
						Официальный сайт финансового уполномоченного в сети "Интернет". Финансовый уполномоченный осуществляет досудебное урегулирование споров между потребителями финансовых услуг и финансовыми организациями.
					</div>
					<br />
					<div>
						<div style="display: flex; align-items: center;">
							<img src="design/{$settings->theme|escape}/img/qr_code_2.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
							Сайт Федеральной службы судебных приставов в сети "Интернет", содержащий форму для подачи жалоб и обращений на нарушение прав и законных интересов физических лиц при осуществлении деятельности по возврату просроченной задолженности физических лиц, возникшей из денежных обязательств.
						</div>
					</div>

				</div>
                {*}
			</div>
		</div>
	</div>
</section>
