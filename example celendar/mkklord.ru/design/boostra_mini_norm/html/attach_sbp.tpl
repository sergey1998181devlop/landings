<div id="block_sbp">
    <div id="main_sbp">
        <h4>Пользуйтесь СБП для удобной оплаты</h4>
        <div class="logo-sector">
            <img class="sbp_logo" src="design/{$settings->theme|escape}/img/sbp_logo.png" alt="СБП"/>
        </div>
        <div class="block_sbp_footer">
            <button class="button green" href="#" id="attach_sbp">Выбрать</button>
        </div>
        <p class="sbp_description">Данные защищены сквозным шифрованием и передаются по безопасному соединению.</p>
    </div>
</div>
<style>
    .block_sbp_footer {
        text-align: center;
    }
    .block_sbp_footer button {
        padding: 0.4em 1.3em;
    }
    #main_sbp {
        width: 300px;
        margin-bottom: 15px;
    }
    #main_sbp>h4{
        font-size: 15px;
    }
    .sbp_logo {
        width: 150px !important;
        height: 30% !important;
        display: inline-block !important;
    }
    .logo-sector {
        text-align: center;
        width: 80%;
    }
    .sbp_description {
        font-size: 11px !important;
        margin: 0 !important;
        text-align: center;
    }
    @media (max-width:480px)
    {
        #main_sbp, .sbp_description {
            width: 100%;
        }
        .sbp_description {
            font-size: 1.3rem;
        }
        .logo-sector>img{
            width: 60% !important;
        }
    }
</style>