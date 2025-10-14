{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.00" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/self_verification.js?v=1.000" type="module"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Идентификация</h1>
                {if !$existTg}
                    {include
                        file='partials/telegram_banner.tpl'
                        margin='20px auto'
						source='nk'
                        tg_banner_text='<h3>Вероятность одобрения может повыситься на 30% <br> Подпишись на наш Telegram канал </h3>'
                        phone={{$phone}}
                    }
                {/if}
			</hgroup>

			<div class="preloader preloader-show"></div>
            <div id="self-validation-container"></div>
		</div>
	</div>
</section>
