<style>
    p {
        font-size: 11px;
        line-height: 2px;
        letter-spacing: 0;
    }

    table {
        border-collapse: collapse;
        padding: 2px;
        vertical-align: middle;
    }

    th {
        border: 1px solid black;
        text-align: center;
        font-size: 12px;
        font-weight: bold;
    }

    td {
        border: 1px solid black;
        text-align: center;
        font-size: 12px;
    }


</style>

<div style="text-align: right; font-weight: bold;">
    <p>Приложение № 2</p>
    <p>к Методике оценки платежеспособности заемщиков</p>
    <p>Общества с ограниченной ответственностью</p>
    <p>Микрокредитная компания «Аквариус»</p>
</div>

<div></div>

<div style="text-align: center; font-weight: bold;">
    <p style="font-size: 12px">ЛИСТ ОЦЕНКИ ПЛАТЕЖЕСПОСОБНОСТИ ЗАЕМЩИКА</p>
</div>
<div style="text-align: center; font-weight: bold;">
    <p>{$full_name}</p>
    <p style="font-size: 9px; font-style: italic;">(ФИО заемщика)</p>
</div>
<table>
    <tr>
        <th colspan="2">ОСНОВНЫЕ ПАРАМЕТРЫ ЗАПРАШИВАЕМОГО ЗАЙМА:</th>
    </tr>
    <tr>
        <td style="width: 200px;">СУММА:</td>
        <td style="font-weight: bold; width: auto;">{$loan_amount}</td>
    </tr>
    <tr>
        <td style="width: 200px;">СРОК:</td>
        <td style="font-weight: bold; width: auto;">{$period} дней</td>
    </tr>
    <tr>
        <td style="width: 200px;">ПРОЦЕНТНАЯ СТАВКА:</td>
        <td style="font-weight: bold; width: auto;">{$percent}</td>
    </tr>
</table>

<br><br><br>

<table>
    <tr>
        <th colspan="2" style="width: 400px; text-align: left">КРИТЕРИЙ</th>
        <th style="width: auto;">РЕЗУЛЬТАТ</th>
    </tr>

    <tr>
        <td style="width: 150px; font-weight: bold;" rowspan="7">ОБЩИЕ <br>СВЕДЕНИЯ<br> ПО ЗАЕМЩИКУ</td>
        <td style="width: 250px;">ВОЗРАСТ</td>
        <td style="width: auto; font-weight: bold;">{$age}</td>
    </tr>
    <tr>
        <td style="width: 250px;">КАТЕГОРИЯ ЗАЕМЩИКА</td>
        <td style="width: auto; font-weight: bold;">ФЛ</td>
    </tr>
    <tr>
        <td style="width: 250px;">МЕСТО ПРОЖИВАНИЯ</td>
        <td style="width: auto; font-weight: bold;">{$fakt_address}</td>
    </tr>
    <tr>
        <td style="width: 250px;">МЕСТО РЕГИСТРАЦИИ</td>
        <td style="width: auto; font-weight: bold;">{$reg_address}</td>
    </tr>
    <tr>
        <td style="width: 250px;">КОНТАКТНЫЙ ТЕЛЕФОН</td>
        <td style="width: auto; font-weight: bold;">{$phone_mobile}</td>
    </tr>
    <tr>
        <td style="width: 250px;">ФАКТ БАНКРОТСТВА</td>
        <td style="width: auto; font-weight: bold;">Отсутствует</td>
    </tr>
    <tr>
        <td style="width: 250px;">НАЛИЧИЕ ИСПОЛНИТЕЛЬНЫХ ПРОИЗВОДСТВ</td>
        <td style="width: auto; font-weight: bold;">0</td>
    </tr>

    <tr>
        <td style="width: 150px; font-weight: bold;" rowspan="4">СООТВЕТСТВИЕ ДОКУМЕНТОВ ОБЩИМ ТРЕБОВАНИЯМ И ИНФОРМАЦИИ ИЗ ОТКРЫТЫХ ИСТОЧНИКОВ</td>
        <td style="width: 250px;">ПАСПОРТ</td>
        <td style="width: auto; font-weight: bold;">{$passport}</td>
    </tr>
    <tr>
        <td style="width: 250px;">СВЕДЕНИЯ О ДЕЙСТВИТЕЛЬНОСТИ ОРГАНИЗАЦИИ РАБОТОДАТЕЛЯ ЗАЕМЩИКА</td>
        <td style="width: auto; font-weight: bold;"></td>
    </tr>
    <tr>
        <td style="width: 250px;">СВЕДЕНИЯ О СООТВЕТСТВИИ
            ЗАЯВЛЕННОЙ ДОЛЖНОСТИ,
            СТАЖА</td>
        <td style="width: auto; font-weight: bold;">{$work_scope}</td>
    </tr>
    <tr>
        <td style="width: 250px;">ЕЖЕМЕСЯЧНЫЙ ДОХОД</td>
        <td style="width: auto; font-weight: bold;">{$income}</td>
    </tr>

    <tr>
        <td style="width: 150px; font-weight: bold;">ФИНАНСОВЫЕ ПОКАЗАТЕЛИ</td>
        <td style="width: 250px;">ПДН</td>
        <td style="width: auto; font-weight: bold;">{$pdn}</td>
    </tr>

    <tr>
        <td style="width: 150px; font-weight: bold;">ВЫЯВЛЕННЫЕ РИСК-ФАКТОРЫ</td>
        <td style="width: auto; text-align: left;" colspan="2">
            1) текущий активный договор займа – отсутствует<br>
            2) информация о недееспособности – отсутствует<br>
            3) нахождение в процедуре банкротства – Отсутствует<br>
            4) наличие исполнительных производств – 0</td>
    </tr>
</table>

<br><br><br><br>

<p style="font-weight: bold;">Присвоенный уровень риска: высокий / <span style="text-decoration: underline;">допустимый</span> (нужное подчеркнуть).</p>

<div></div><div></div>

<p>В соответствии с вышеизложенным рекомендуется принять решение:</p>
<p>• удовлетворить заявление заемщика и предоставить запрашиваемый заем</p>

<div></div><div></div>

<table>
    <tr>
        <th colspan="2">ОСНОВНЫЕ ПАРАМЕТРЫ ОДОБРЕННОГО ЗАЙМА:</th>
    </tr>
    <tr>
        <td style="width: 200px;">СУММА:</td>
        <td style="font-weight: bold; width: auto;">{$approved_loan_amount}</td>
    </tr>
    <tr>
        <td style="width: 200px;">СРОК:</td>
        <td style="font-weight: bold; width: auto;">{$period} дней</td>
    </tr>
    <tr>
        <td style="width: 200px;">ПРОЦЕНТНАЯ СТАВКА:</td>
        <td style="font-weight: bold; width: auto;">{$percent}</td>
    </tr>
</table>

<br><br><br><br>

<table>
    <tr>
        <td style="width: 150px; border: 1px solid white">ДАТА: {$issuance_date}<br><hr></td>
        <td style="width: 50px; border: 1px solid white"></td>
        <td style="width: auto; border: 1px solid white"> ВЕРИФИКАТОР: {$verificator}<br><hr></td>
    </tr>
</table>
