<div class="nav">
    <ul>
        <li><a href="/user?user_id={$user->id}" {if $current=='user'}class="current"{/if}>Текущий заём</a></li>
        {if $restricted_mode !== 1}
            <li><a href="/user/upload" {if $current=='upload'}class="current"{/if}>Мои файлы</a></li>
            <li><a href="/user/docs" {if $current=='docs'}class="current"{/if}>Документы</a></li>
{*            <li><a href="/user/faq" {if $current=='faq'}class="current"{/if}>Вопросы и ответы</a></li>*}
            <li class="nav-tickets">
                <a href="/user/tickets" {if $current=='tickets'}class="current"{/if}>
                    Форма обращения
                    <span class="nav-alert" id="operator-alert" title="Есть непрочитанные комментарии"></span>
                </a>
            </li>
            <li><a href="user/logout">Выйти</a></li>
        {/if}
    </ul>
</div>

{literal}
    <script>
        document.addEventListener('DOMContentLoaded', updateTicketsUnreadCommentsAlert);
    </script>
{/literal}