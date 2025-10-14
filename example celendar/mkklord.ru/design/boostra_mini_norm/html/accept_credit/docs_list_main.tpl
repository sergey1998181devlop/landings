{if $user_order['organization_id'] == $ORGANIZATION_FINLAB}
    {$accept_documents = [
    'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/finlab/Obshchie-usloviya-OOO-MKK-FINLAB-ot-01.06.2024.docx', 'docname' => 'Общими условиями договора потребительского микрозайма', 'class' => ''],
    'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/finlab/Pravila-predostavleniya-zajmov-01.06.2024-FINLAB.docx', 'docname' => "Правилами предоставления займов ООО МКК «{$config->org_name}»", 'class' => ''],
    'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=11', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
    'agreed_5' => ['verify' => 1, 'filename' => '/user/docs?action=micro_zaim&organization_id=11', 'docname' => 'Заявлением о предоставлении микрозайма', 'link_class' => 'micro-zaim-doc-js'],
    'agreed_6' => ['verify' => 1, 'filename' => "/files/docs/finlab/Politika-konfidencial'nosti.docx", 'docname' => "Политикой конфиденциальности ООО МКК «{$config->org_name}»", 'class' => ''],
    'credit_doctor_checkbox' => [],
    'star_oracle' => [],
    'agreed_7' => ['verify' => 1, 'filename' => '/user/docs?action=soglasie_na_bki_finlab', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
    'agreed_8' => ['verify' => 1, 'filename' => '/files/docs/finlab/Politika-bezopasnosti-platezhej-Best2Pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
    'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
    'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value', 'show_only_safety_flow' => true]
    ]}
{elseif $user_order['organization_id'] == $ORGANIZATION_VIPZAIM}
    {$accept_documents = [
    'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/viploan/obshchie-usloviya-ooo-mkk-vipzai-m-ot-01-06-2024.docx', 'docname' => 'Общими условиями договора потребительского микрозайма', 'class' => ''],
    'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/viploan/pravila-predostavleniya-zai-mov-01-06-2024-vipzai-m.docx', 'docname' => 'Правилами предоставления займов ООО МКК «ВИПЗАЙМ»', 'class' => ''],
    'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=12', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
    'agreed_5' => ['verify' => 1, 'filename' => '/files/docs/viploan/zayavlenie-o-predostavlenii-mikrozai-ma-vipzai-m.docx', 'docname' => 'Заявлением о предоставлении микрозайма', 'class' => ''],
    'agreed_6' => ['verify' => 1, 'filename' => "/files/docs/viploan/politika-konfidencialnosti.docx", 'docname' => 'Политикой конфиденциальности ООО МКК «ВИПЗАЙМ»', 'class' => ''],
    'credit_doctor_checkbox' => [],
    'star_oracle' => [],
    'agreed_8' => ['verify' => 1, 'filename' => 'files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
    'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
    'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value', 'show_only_safety_flow' => true]
    ]}
{else}
    {$accept_documents = [
    'agreed_1' => ['verify' => 1, 'filename' => "{$config->root_url}/share_files/docs/accept_documents/obschie-usloviya.pdf", 'docname' => 'Общие условия договора займа'],
    'agreed_2' => ['verify' => 1, 'filename' => "{$config->root_url}/share_files/docs/accept_documents/pravila-predostavleniya.pdf", 'docname' => 'Правила предоставления займов'],
    'agreed_3' => ['verify' => 1, 'filename' => "{$config->root_url}/share_files/register_user_docs/polozhenie-asp.pdf", 'docname' => 'Положение АСП'],
    'agreed_4' => ['verify' => 1, 'filename' => "user/docs?action=micro_zaim&organization_id={$ORGANIZATION_DEFAULT}", 'docname' => 'Заявлением о предоставлении микрозайма'],
    'agreed_5' => ['verify' => 1, 'filename' => "{$config->root_url}/share_files/get_loan_user_docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf", 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
    'agreed_6' => ['verify' => 0, 'filename' => "{$config->root_url}/user/docs?action=preview_fin_doctor&order_id={$user_order['id']}", 'docname' => 'Заявление на услугу Финансовый Доктор'],
    'agreed_7' => ['verify' => 0, 'filename' => "{$config->root_url}/share_files/docs/accept_documents/Dogovor-oferta.pdf", 'docname' => 'Оферта Финансовый Доктор']
    ]}
{/if}

<div class="docs_wrapper">
    <p class="toggle-conditions-accept toggle-conditions-accept">Я согласен со всеми условиями:
        <span class="arrow">
            <img src="{$config->root_url}/design/boostra_mini_norm/img/icons/chevron-svgrepo-com.svg" alt="Arrow" />
        </span>
    </p>
    <div class="conditions">
        {foreach $accept_documents as $accept_document_key => $accept_document}
            {if $accept_document_key == 'agreed_9'}
                {if $notOverdueLoan || $isSafetyFlow || $applied_promocode->disable_additional_services}
                    {continue}
                {/if}
            {/if}

            {if $accept_document_key == 'credit_doctor_checkbox'}
                {include file="credit_doctor/credit_doctor_checkbox.tpl" idkey=$user_order['id']}
                {continue}
            {/if}
            {if $accept_document_key == 'star_oracle'}
                {include file="star_oracle/star_oracle_checkbox.tpl" idkey=$user_order['id']}
                {continue}
            {/if}

            {if $accept_document_key == 'agreed_10' && isset($accept_document['show_only_safety_flow']) && $accept_document['show_only_safety_flow'] && !$isSafetyFlow}
                {continue}
            {/if}

            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 10px !important;height: 10px !important;">
                        <input class="{if $accept_document['verify']}js-need-verify{/if} {$accept_document['class']}" type="checkbox" value="0" id="{$accept_document_key}"/>
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                <p>{if $accept_document_key == 'agreed_10'}Настоящим выражаю свое согласие{else} Настоящим подтверждаю, что полностью ознакомлен и согласен с{/if}
                    {if isset($accept_document['filename']) && $accept_document['filename']}
                        <a href="{$accept_document['filename']}" class="{$accept_document['link_class']}" target="_blank">
                            {$accept_document['docname']}
                        </a>
                    {else}
                        {$accept_document['docname']}
                        {if $accept_document_key == 'agreed_9'}
                            <a type="button" class="pointer" id="btn-modal-telemed" data-modal="btn-modal-telemed">Подробнее</a>
                        {/if}
                    {/if}
                </p>
            </div>
        {/foreach}

        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 10px !important;height: 10px !important;">
                    <input class="js-service-doctor js-need-verify" type="checkbox" value="1" id="service_doctor_check" name="service_doctor"/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a class="contract_approve_file" href="{$config->root_url}/files/contracts/{$user_order['approved_file']}" target="_blank">Договором</a></p>
        </div>
        <div id="not_checked_info" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором и нажать "Получить деньги"</strong>
        </div>
    </div>
</div> 