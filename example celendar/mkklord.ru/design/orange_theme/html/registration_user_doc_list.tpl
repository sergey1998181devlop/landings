{assign var="userInitDocs" value=[
    ["name" => "Положение АСП", "url" => "{$config->root_url}/share_files/register_user_docs/polozhenie-asp.pdf"],
    ["name" => "Согласие на обработку паспортных данных", "url" => "{$config->root_url}/share_files/register_user_docs/soglasie-na-obrabotku-pd.pdf"],
    ["name" => "Получение маркетинговых коммуникаций", "url" => "{$config->root_url}/share_files/register_user_docs/poluchenie-marketingovyh-kommunikacij.pdf"],
    ["name" => "Согласие БКИ", "url" => "{$config->root_url}/share_files/register_user_docs/soglasie-bki.pdf"]
]}

{if $module == 'MainView'}
    {foreach $userInitDocs as $doc}
        {if $doc@iteration == 4}
            {continue}
        {/if}
        <li>
            <a href="{$doc.url}" target="_blank">{$doc.name}</a>
        </li>
    {/foreach}
{elseif $module == "UserView"}
<div class="docs_wrapper">
    <p class="toggle-conditions">Я согласен со всеми условиями:
        <span class="arrow">
            <img src="{$config->root_url}/design/boostra_mini_norm/img/icons/chevron-svgrepo-com.svg" alt="Arrow"/>
        </span>
    </p>
    <div class="conditions">
        <div id="not_checked_info" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором</strong>
        </div>
        {foreach $userInitDocs as $key => $doc}
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 10px !important;height: 10px !important;">
                        <input class="js-agreeed-asp js-need-value" type="checkbox"
                               id="agreed_{$key}"
                               name="agreed_{$key}" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с <a href="{$doc.url}" target="_blank">{$doc.name}</a></p>
            </div>
        {/foreach}
    </div>
</div>
{else}
    <div>
        <ol>
            {foreach $userInitDocs as $doc}
                <li>
                    <a href="{$doc.url}" target="_blank" class="text-red fw-bold">{$doc.name}</a>
                </li>
            {/foreach}
        </ol>
    </div>
{/if}

