{* Главная страница магазина *}

{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = 'landing.tpl' scope=parent}

{* Канонический адрес страницы *}
{$canonical="" scope=parent}

<section class="section section-welcome bg-cover bg-sail-100 text-white pt-16">
    <div class="container">
        <div class="flex flex-wrap items-center">
            <div>
                <h2 class="text-5xl font-bold text-primary mb-4">Мгновенный займ не выходя из дома</h2>
                <p class="text-xl text-topaz-600 max-w-3xl">Привяжите вашу карту и пользуйтесь всеми возможностями займов, не выходя из дома</p>
            </div>
            <div class="w-full lg:w-1/2">
                <div>
                    <div class="section-welcome__calculator calculator">
                        <div class="calculator-item slider-container">
                            <div class="calculator-item__header">
                                <span class="calculator-item__title">Мне нужно:</span>
                                <span class="calculator-item__amount">
                        <span class="slider-current-value">30000</span>
                        <span>₽</span>
                      </span>
                            </div>
                            <div class="calculator-item__values">
                                <span class="calculator-item__min">1000</span>
                                <span class="calculator-item__max">30000</span>
                            </div>
                            <div class="calculator-slider slider" data-current-value="30000" data-min="1000" data-max="30000" data-step="1000" style="cursor: grab;">
                                <div class="calculator-slider__line slider">
                                    <div class="calculator-slider__progress slider-progress" style="width: 36.7089%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="calculator-item slider-container">
                            <div class="calculator-item__header">
                                <span class="calculator-item__title">Срок займа:</span>
                                <span class="calculator-item__amount">
                        <span class="slider-current-value">16</span>
                        <span>дней</span>
                      </span>
                            </div>
                            <div class="calculator-item__values">
                                <span class="calculator-item__min">5</span>
                                <span class="calculator-item__max">16</span>
                            </div>
                            <div class="calculator-slider slider" data-current-value="16" data-min="5" data-max="16" data-step="1" style="cursor: grab;">
                                <div class="calculator-slider__line slider">
                                    <div class="calculator-slider__progress slider-progress" style="width: 8%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="calculator__request-money-link" id="request-loan-btn">Оформить заявку</button>
                        <div class="calculator__guarantee-text">Мы гарантируем автоматическое одобрение заявки и высокую степень безопасности данных</div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/2 pl-24 girl_wrapper">
                <img class="max-w-md w-full" src="/design/{$settings->theme|escape}/assets/image/girls/{$config->org_main_img}">
            </div>
        </div>
    </div>
</section>
<section class="section section-gray section-timer">
    <div class="container">
        <div class="section-timer__block">
            <div class="section-timer__text">
                <h2 class="title">Успей взять займ под <span class="percent"><span class="percent-number">0</span><span class="ml-2">%</span></span></h2>
                <span>Предложение пропадёт через</span>
            </div>
            <div id="timer" class="section-timer__timer font-bold">00:00</div>
        </div>
    </div>
</section>
<section id="steps" class="section section-steps bg-water-100">
    <div class="container">
        <div class="section-steps__block">
            <h2 class="title text-primary">Как получить заем</h2>
            <span>Для получения займа на нашем сервисе вам нужно пройти несколько простых шагов</span>
            <div class="section-steps__steps">
                <div class="section-steps-stepItem">
                    <div class="section-steps-stepItem__number">1</div>
                    <img class="section-steps-stepItem__image" src="/design/{$settings->theme|escape}/assets/image/steps/step-1.svg">
                    <span class="section-steps-stepItem__name">Зарегистрируйтесь на сайте</span>
                </div>
                <div class="section-steps-stepItem">
                    <div class="section-steps-stepItem__number">2</div>
                    <img class="section-steps-stepItem__image" src="/design/{$settings->theme|escape}/assets/image/steps/step-2.svg">
                    <span class="section-steps-stepItem__name">Заполните анкету в личном кабинете</span>
                </div>
                <div class="section-steps-stepItem">
                    <div class="section-steps-stepItem__number">3</div>
                    <img class="section-steps-stepItem__image" src="/design/{$settings->theme|escape}/assets/image/steps/step-3.svg">
                    <span class="section-steps-stepItem__name">Выберите сумму и срок займа</span>
                </div>
                <div class="section-steps-stepItem">
                    <div class="section-steps-stepItem__number">4</div>
                    <img class="section-steps-stepItem__image" src="/design/{$settings->theme|escape}/assets/image/steps/step-4.svg">
                    <span class="section-steps-stepItem__name">Получите нужную сумму</span>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="about" class="section section-about section-gray">
    <div class="section-about__block container">
        <div class="flex items-center"><span class="text-primary mr-4"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 50 50" width="50px" height="50px" class="fill-current"><path d="M 17 5 C 14.250484 5 12 7.2504839 12 10 L 12 12 L 10 12 C 7.2504839 12 5 14.250484 5 17 L 5 40 C 5 42.749516 7.2504839 45 10 45 L 33 45 C 35.749516 45 38 42.749516 38 40 L 38 38 L 40 38 C 42.749516 38 45 35.749516 45 33 L 45 10 C 45 7.2504839 42.749516 5 40 5 L 17 5 z M 17 7 L 40 7 C 41.668484 7 43 8.3315161 43 10 L 43 33 C 43 34.668484 41.668484 36 40 36 L 38 36 L 36 36 L 17 36 C 15.331516 36 14 34.668484 14 33 L 14 23 C 14 21.883334 14.883334 21 16 21 L 19 21 L 19 27 C 19 29.197334 20.802666 31 23 31 L 29 31 L 31 31 L 34 31 C 36.197334 31 38 29.197334 38 27 L 38 17 C 38 14.250484 35.749516 12 33 12 L 14 12 L 14 10 C 14 8.3315161 15.331516 7 17 7 z M 10 14 L 12 14 L 14 14 L 33 14 C 34.668484 14 36 15.331516 36 17 L 36 27 C 36 28.116666 35.116666 29 34 29 L 31 29 L 31 23 C 31 20.802666 29.197334 19 27 19 L 21 19 L 19 19 L 16 19 C 13.802666 19 12 20.802666 12 23 L 12 33 C 12 35.749516 14.250484 38 17 38 L 36 38 L 36 40 C 36 41.668484 34.668484 43 33 43 L 10 43 C 8.3315161 43 7 41.668484 7 40 L 7 17 C 7 15.331516 8.3315161 14 10 14 z M 21 21 L 27 21 C 28.116666 21 29 21.883334 29 23 L 29 29 L 23 29 C 21.883334 29 21 28.116666 21 27 L 21 21 z"/></svg></span><h2 class="title text-primary">Сервис {$config->org_name}</h2></div><p>Наша компания работает для активных и ответственных людей, ценящих свое время, желающих зарабатывать и умеющих тратить, идущих к своей цели, но стремящихся жить здесь и сейчас.</p>
        <p>Мы оперативно решаем вопрос предоставления займа, не требуем залогов и лишних документов, предлагаем прозрачные индивидуальные условия сотрудничества. Наши средства максимально доступны для предпринимателей с четким планом действий, а также для тех, кто умеет «держать слово».</p>
    </div>
</section>
<section id="registration" class="section section-instant-loan">
    <div class="section-instant-loan__block container">
        <h2 class="title">Мгновенный займ не выходя из дома</h2>
        <span>Доступен при плохой кредитной истории, без справок и проверок, выдаётся за 15 минут!</span>
        <a class="section-instant-loan__block-link" href="#">Зарегистрироваться</a>
    </div>
</section>
<section id="documents" class="section section-documents">
    <div class="section-documents__block container">
        <h2 class="title">Документы</h2>
        <ul>
            <li><a target="_blank" href="/files/docs/lord/1-extract_from_state_register_of_microfinance_organizations.pdf">Выписка из государственного реестра МФО</a></li>
            <li><a target="_blank" href="/files/docs/lord/2-extract_from_register_of_sro_members.pdf">Выписка из реестра членов СРО</a></li>
            <li><a target="_blank" href="/files/docs/lord/3-tax_identification_number_certificate.pdf">Свидетельство ИНН</a></li>
            <li><a target="_blank" href="/files/docs/lord/4-charter.pdf">Устав</a></li>
            <li><a target="_blank" href="/files/docs/lord/5-personal_data_processing_and_storage_policy.pdf">Политика обработки и хранения персональных данных.pdf</a></li>
            <li><a target="_blank" href="/files/docs/lord/6-agreement_on_use_of_handwritten_signature_analogue.pdf">Соглашение об использовании аналога собственноручной подписи</a></li>
            <li><a target="_blank" href="/files/docs/lord/7-general_terms_of_loan_agreement.pdf">Общие условия договора займа</a></li>
            <li><a target="_blank" href="/files/docs/lord/8-rules_for_providing_loans.pdf">Правила предоставления займов</a></li>
            <li><a target="_blank" href="/files/docs/lord/9-information_for_financial_services_recipients.pdf">Информация для получателей финансовых услуг</a></li>
            <li><a target="_blank" href="/files/docs/lord/10-privacy_policy.pdf">Политика конфиденциальности</a></li>
            <li><a target="_blank" href="/files/docs/lord/11-procedure_for_reviewing_appeals_of_financial_services_recipients.pdf">Порядок рассмотрения обращений получателей финансовых услуг</a></li>
            <li><a target="_blank" href="/files/docs/lord/12-baseline_standard_for_protecting_rights_and_interests_of_financial_services_recipients.pdf">Базовый стандарт защиты прав и интересов
                    получателей финансовых услуг</a></li>
            <li><a target="_blank" href="/files/docs/lord/13-baseline_standard_for_risk_management_of_microfinance_organizations.pdf">Базовый стандарт по управлению рисками микрофинансовых
                    организаций</a></li>
            <li><a target="_blank" href="/files/docs/lord/14-baseline_standard_for_microfinance_organizations_operations_on_the_financial_market.pdf">Базовый стандарт совершения МФО операций на
                    финансовом рынке</a></li>
            <li><a target="_blank" href="/files/docs/lord/15-law_of_the_russian_federation_07_02_1992_no_2300-1_on_protection_of_consumer_rights.pdf">Закон РФ от 07.02.1992 № 2300-1 'О защите прав
                    потребителей'</a></li>
            <li><a target="_blank" href="/files/docs/lord/16-information_brochure_of_the_bank_of_russia_on_microfinance_organizations.pdf">Информационная брошюра Банка России об МФО</a></li>
            <li><a target="_blank" href="/files/docs/lord/17-information_on_submitting_an_appeal_to_fu.pdf">Информация о подаче обращения в адрес ФУ</a></li>
            <li><a target="_blank" href="/files/docs/lord/18-information_on_risks_of_access_to_protected_information.pdf">Информация о рисках доступа к защищаемой информации</a></li>
            <li><a target="_blank" href="/files/docs/lord/19-offer_on_using_the_best2pay_processing_center.pdf">Оферта об использовании процессного центра BEST2PAY</a></li>
            <li><a target="_blank" href="/files/docs/lord/20-best2pay_payment_security_policy.pdf">Политика безопасности платежей BEST2PAY</a></li>
            <li><a target="_blank" href="/files/docs/lord/21-memo_of_the_bank_of_russia_on_credit_holidays_for_svo_participants.pdf">Памятка Банка России о кредитных каникулах для участников СВО</a>
            </li>
            <li><a target="_blank" href="/files/docs/lord/22-information_on_credit_holidays_federal_law_353-fz.pdf">Информация о кредитных каникулах 353-ФЗ</a></li>
            <li><a target="_blank" href="/files/docs/lord/23-information_on_credit_holidays_federal_law_377-fz.pdf">Информация о кредитных каникулах 377-ФЗ</a></li>
            <li><a target="_blank" href="/files/docs/lord/24-list_of_third_parties_to_whom_user_data_is_transferred.pdf">Перечень третьих лиц, которым передаются пользовательские данные</a></li>
            <li><a target="_blank" href="/files/docs/lord/25-links_to_site_pages_used_for_client_acquisition.pdf">Ссылки на страницы сайтов, используемых для привлечения клиентов</a></li>
        </ul>
    </div>
</section>
