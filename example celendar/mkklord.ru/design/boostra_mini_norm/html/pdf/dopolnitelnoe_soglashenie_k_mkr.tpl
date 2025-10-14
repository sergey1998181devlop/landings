<tr>
    <td width="100%" style="text-align: center">
        <strong>ДОПОЛНИТЕЛЬНОЕ СОГЛАШЕНИЕ К ДОГОВОРУ МИКРОЗАЙМА № ? от ? г.</strong>
    </td>
</tr>
<table>
    <tr>
        <td style="width: 20%"><strong>г. Самара</strong></td>
        <td style="width: 70%"></td>
        <td style="width: 20%"><strong>1998</strong></td>
    </tr>
</table>
<div style="font-size: 11px;">
    <p align="justify" style="line-height: 1.6">
        Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape} {$birth|date} года рождения, паспорт гражданина
        Российской Федерации; серия {$passport_serial|escape}, номер
        {$passport_number|escape}, выдан {$passport_issued|escape} от {$passport_date|date} , зарегистрирован(-на) по
        адресу: {$regindex|escape}, {$regregion|escape}, {$regcity|escape}, {$regstreet|escape} ул,
        д. {$reghousing|escape}, кв. {$regroom|escape} («Заемщик») выражаю Микрокредитной компаниии ООО «{$config->org_name}»
        (зарегистрировано в реестре
        микрофинансовых организаций за номером 1703336008323 от 6 июня 2017 года, ИНН/ОГРН  {$config->org_inn}/ {$config->org_ogrn},
        юридический
        адрес: 443099, Самарская область, г.Самара, ул. Фрунзе д. 10), («Кредитор») согласие на
        изменение Индивидуальных
        условий договора займа № ? от ? г.
    </p>
    <p style="text-align: center"><strong>
            Измененные индивидуальные условия договора микрозайма
        </strong>
    </p>
    <table border="0.5" cellpadding="3" style="font-size: 10px">
        <thead>
        <tr style="font-weight: bold;">
            <td width="30" align="center">№</td>
            <td width="200" align="center">Условие</td>
            <td width="308" align="center">Содержание условия</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td width="30" align="center">1</td>
            <td width="200">Сумма займа</td>
            <td width="308">Сумма займа составляет: ?</td>
        </tr>
        <tr>
            <td width="30" align="center">2</td>
            <td width="200">Срок действия договора, срок возврата займа</td>
            <td width="308">Настоящий договор микрозайма вступает в силу с момента передачи
                денежных средств Заемщику или поступления денежных средств на счет
                Заемщика, открытый в кредитной организации, зарегистрированной на
                территории Российской Федерации, и действует до полного исполнения
                сторонами обязательств по нему (фактического возврата займа в полном
                объеме). Микрозайм подлежит возврату {$loan_last_day} г.
            </td>
        </tr>
        <tr>
            <td width="30" align="center">3</td>
            <td width="200">Процентная ставка</td>
            <td width="308">365,000 (Триста шестьдесят пять) процентов годовых (1 % в день)</td>
        </tr>
        <tr>
            <td width="30" align="center">4</td>
            <td width="200">Количество, размер и периодичность (сроки)
                платежей Заемщика по договору или порядок
                определения этих платежей
            </td>
            <td width="308">Возврат сумма займа и процентов подлежат оплате единовременным
                платежом в срок, указанный в п. 1 настоящих измененных
                индивидуальных условий.
                Размер платежа (задолженности) к моменту возврата займа составит
                ? руб.
            </td>
        </tr>
        <tr>
            <td width="30" align="center">5</td>
            <td width="200">Условие установления нового срока возврата
            </td>
            <td width="308">В день подписания измененных индивидуальных условий договора
                микрозайма Заемщик осуществляет погашение процентов за
                пользование суммой займа, начисленных на дату подписания настоящих
                измененных индивидуальных условий в сумме ? руб. Срок возврата
                займа не меняется при невыполнении указанного условия.
            </td>
        </tr>
        <tr>
            <td width="30" align="center">6</td>
            <td width="200">Порядок изменения количества, размера и
                периодичности (сроков) платежей Заемщика при
                частичном досрочном возврате займа
            </td>
            <td width="308">Проценты начисляются на оставшуюся непогашенную часть суммы займа
                со дня, следующего за днем частичного погашения. Оставшаяся
                задолженность в полном объеме должна быть погашена в дату,
                указанную в п. 2 настоящей таблицы
            </td>
        </tr>
        <tr>
            <td width="30" align="center">7</td>
            <td width="200">Лицо, подписавшее настоящее измененные
                индивидуальные условия от имени Кредитора
            </td>
            <td width="308">Директор {$config->org_director} на основании Устава от {$config->org_date_charter} г.
            </td>
        </tr>

        <tr>
            <td width="30" align="center">8</td>
            <td width="200">Дата подписания настоящих измененных
                индивидуальных условий
            </td>
            <td width="308">?
            </td>
        </tr>
        <tr style="height: 400px">
            <td width="30" align="center">9</td>
            <td width="200">Подписи сторон</td>
            <td width="308">
                <span>Кредитор:</span>
                <span>_____</span>
                <span>Директор МКК ООО "{$config->org_name}" {$config->org_director}</span><br>
                <span>Заемщик:</span>
                <span>_____</span>
                <span>{$lastname|escape} {$firstname|escape} {$patronymic|escape}</span><br>
                <small>(Подписано АСП {$asp|escape})</small>
            </td>
        </tr>
        <tr>
            <td>
                <img src="{$config->root_url}/design/boostra_mini_norm/html/pdf/i/stamp_boostra.png" width="100">
            </td>
        </tr>
        </tbody>
    </table>
</div>