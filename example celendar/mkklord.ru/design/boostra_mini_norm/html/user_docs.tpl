{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{literal}


{/literal}

<link href="design/{$settings->theme|escape}/css/user_docs__extra_services.css" rel="stylesheet" type="text/css" >

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

            {include file='user_nav.tpl' current='docs'}

			<div class="content">
				<div class="panel">
{*                    {if $userHasDocuments}*}
{*                        <button class="download" style="float: left; margin-left: 60px; cursor: pointer;">*}
{*                            Скачать документы*}
{*                        </button>*}
{*                    {/if}*}
{*                    <div id="loading" style="display:none;">Ожидайте, архив формируется...</div>*}
                    <p>{$credit_rating_paid_message}</p>
                    {if $page_error || in_array($user->id, [1530250,1248134])}
                        <div>
                            <h2>Раздел сейчас не доступен!</h2>
                            <br />
                            <h4>Попробуйте зайти на эту страницу позже.</h4>
                        </div>
                    {else}
                        {* Активные займы *}
                        {foreach $order_docs as $loan => $data}
                            {if $data['hidden'] || $data['is_closed']}{continue}{/if}
                            <div class="loan_docs" {if !$current_loan || $current_loan != $loan}data-hidden="1"{/if}>
                                <h2 class="js-toggle-loan-docs"><ins>{$loan}{if $data['date']} от {$data['date']}{/if}</ins> <span>Нажмите, чтобы раскрыть</span></h2>
                                <div class="loan_docs_list">
                                    <ul class="docs_list">
                                        {foreach $data['crm'] as $doc}
                                            {if $doc && $doc->name}
                                                <li>
                                                    {if !$doc->replaced}
                                                        <a href="{$config->root_url}/document/{$doc->user_id}/{$doc->id}.pdf" target="_blank">
                                                            {$doc->name|escape}
                                                        </a>
                                                    {else}
                                                        <a href="/files/doc/doc_id_{$doc->id}.pdf"
                                                           target="_blank">{$doc->name|escape}</a>
                                                    {/if}
                                                </li>
                                            {/if}
                                        {/foreach}
                                        {foreach $data['1c'] as $doc}
                                            {if $doc->uid != '19e7e23e-4ea3-426f-8f36-86deff750c38'}
                                                <li>
                                                    <a href="{$config->root_url}/user/docs/{$doc->uid}" target="_blank">
                                                        {$doc->name|escape}
                                                    </a>
                                                </li>
                                            {/if}
                                        {/foreach}
{*                                        {foreach $data['asp'] as $doc}*}
{*                                            <li>*}
{*                                                <a href="files/asp/{$doc->file_name}"*}
{*                                                   target="_blank">*}
{*                                                    Согласие субъекта на иные способы и частоту взаимодействия от {$doc->date_added|date} по займу №{$doc->zaim_number}*}
{*                                                </a>*}
{*                                            </li>*}
{*                                        {/foreach}*}
                                        {*{foreach $data['balance'] as $doc}
                                            <li>
                                                <a href="user/details/{$doc['НомерЗайма']}" target="_blank">
                                                    Расшифровка по займу {$doc['НомерЗайма']}
                                                </a>
                                            </li>
                                        {/foreach}*}
                                        {foreach $data['uploaded'] as $doc}
                                            <li>
                                                <a href="files/uploaded_files/{$doc->name}" target="_blank">
                                                    {$doc->name}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                    <div class="js-expand-loan-docs"></div>
                                </div>
                            </div>
                        {/foreach}
                        {* Закрытые займы *}
                        {assign var="hasClosedLoans" value=false}
                        {foreach $order_docs as $loan => $data}
                            {if $data['hidden'] || !$data['is_closed']}{continue}{/if}

                            {if !$hasClosedLoans}
                                {assign var="hasClosedLoans" value=true}
                                <h2 style="text-align: left; margin-left: 60px;">Закрытые займы</h2>
                            {/if}

                            <div class="loan_docs closed_loan_name">
                                <h3 style="text-align: left; margin-left: 60px;">{$loan}</h3>
                            </div>
                        {/foreach}

                        {if !empty($order_docs)}
                            <h2 style="text-align: left; margin-left: 60px;">Остальные документы</h2>
                        {/if}

                        <ul class="docs_list">
                            {if $doc_bki}
                                <li>
                                    <a href="{$doc_bki}" target="_blank">
                                        Согласие клиента на получение информации из бюро кредитных историй
                                    </a>
                                </li>
                            {/if}

                            {if $user->id == 56219}
                            <li>
                                <a href="{$config->root_url}/files/specials/v8_6194_34.pdf" target="_blank">
                                    Уведомление о цессии
                                </a>
                            </li>
                            {/if}

                            {if $user->id == 664871}
                            <li>
                                <a href="{$config->root_url}/files/doc/Договор.pdf" target="_blank">
                                    Договор
                                </a>
                            </li>
                            {/if}

                            {if $user->id == 592934}
                                <li>
                                    <a href="{$config->root_url}/files/doc/Dopolnitelnoe_soglashenie_Gavrilova_A.V.pdf" target="_blank">
                                        Дополнительное соглашение Гаврилова А.В
                                    </a>
                                </li>
                            {/if}

                            {foreach $paid_loan_references as $paid_loan_reference}
                                <li>
                                    <a href="/document/{$user->id|escape:'url'}/{$paid_loan_reference['loan_document_id']|escape:'url'}.pdf"
                                       target="_blank">
                                        Справка о погашении займа №{$paid_loan_reference['number']}
                                    </a>
                                </li>
                            {/foreach}

                            {foreach $crm_docs as $crm_doc}
                                {if $crm_doc && $crm_doc->name}
                                    <li>
                                        {if !$crm_doc->replaced}
                                            <a href="{$config->root_url}/document/{$crm_doc->user_id}/{$crm_doc->id}.pdf" target="_blank">
                                                {$crm_doc->name|escape}
                                                {if in_array($crm_doc->type,['PRICINA_OTKAZA_I_REKOMENDACII','ZAYAVLENIYE_OTKAZA_REKOMENDACII'])}
                                                    {$crm_doc->order_id}
                                                {/if}
                                            </a>
                                        {else}
                                            <a href="/files/doc/doc_id_{$crm_doc->id}.pdf"
                                                 target="_blank">{$crm_doc->name|escape}</a>
                                        {/if}
                                    </li>
                                {/if}
                            {/foreach}
{*                            {if !empty($additional_action_2)}*}
{*                                <li>Уведомление*}
{*                                    <a href="user/docs?action=additional_service_2"*}
{*                                       target="_blank">*}
{*                                        О предоставлении дополнительных услуг*}
{*                                    </a>*}
{*                                </li>*}
{*                            {/if}*}
                            {if $loan_history}
                                <li><a id="link-references">Справки</a></li>
                                <li><a id="link-cessii">Цессии и Агентские договоры</a></li>
                            {/if}
                        </ul>

                        {if $loan_history}
                            {include file='user_docs__references.tpl'}
                            {include file='user_docs__notices_of_assigment.tpl'}
                        {/if}

                    {/if}
				</div>

			</div>
		</div>
	</div>
</section>
<script>
    var userId = '{$user_id}';
</script>
    {literal}
        <script type="text/javascript">

            $('.download').click(function() {
                $('#loading').show();
                var url = 'ajax/download_documents.php?action=download_zip&user_id=' + userId;
                var downloadTimer = setInterval(function() {
                    if (document.readyState === 'complete') {
                        clearInterval(downloadTimer);
                        $('#loading').hide();
                    }
                }, 2000);
                window.location.href = url;
            });

            $('.js-toggle-loan-docs').click(function() {
                let $loan_docs = $(this).closest('.loan_docs');
                let isHidden = $loan_docs.attr('data-hidden') === '1';
                $loan_docs.attr('data-hidden', isHidden ? '0' : '1');
            });

            $('.js-expand-loan-docs').click(function() {
                let $loan_docs = $(this).closest('.loan_docs');
                $loan_docs.attr('data-hidden', '0');
            });
        </script>
    {/literal}
</section>
