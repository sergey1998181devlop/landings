{* Страница заказа *}

{$meta_title = "Заявка на заём | {$config->org_name}" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/files_data.app.js?v=1.69" type="text/javascript"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
                <h3 style="color: #008000FF;display: none;">Карта успешно привязана.</h3>
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
{*				<h5>Остался последний шаг! Прикрепите фотографии.</h5>*}
			</hgroup>

            {include file='display_stages.tpl' current=5 percent=85 total_step=5}
            
                <form id="files_form" method="POST" enctype="multipart/form-data" >
                    
                <input type="hidden" name="stage" value="add_files" />
                
                {if $error=='error_upload'}
                <div class="alert alert-danger">
                    При передаче файлов произошла ошибка, попробуйте повторить позже.
                </div>
                {/if}
                
                <div class="js-error-block payment-block error" style="display:none">
                    <div class="payment-block-error">
                        <p>Ошибка при передаче</p>
                        <a href="/" class="button big button-inverse cancel_payment">Закончить</a>
                    </div>
                </div>
                
                <div id="file_form">
                    
                    <fieldset class="passport1-file file-block">
                        
                        <legend>Разворот главной страницы паспорта (2-3 стр.)</legend>
    
                        <div class="alert alert-danger " style="display:none"></div>
                        
                        <div class="user_files">
                            {if $passport1_file}                                                
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="{$passport1_file->name|resize:100:100}" />
                                </div>
                                <span class="js-remove-file" data-id="{$passport1_file->id}"
                                      {if !empty($hide_delete_passport_photo_button)}style="visibility: hidden"{/if}>Удалить</span>
                                <input type="hidden" id="passport1" name="user_files[]" value="{$passport1_file->id}" />
                            </label>
                            {/if}
                        </div>
                        
                        <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                            <div class="file-label">
                                <label for="user_file_passport1" class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/passport1.png" />
                                </label>

                                <label onclick="sendMetric('reachGoal', 'get_user_photo_3');"  class="get_mobile_photo photo_btn not-visible-sm" for="user_file_passport1">
                                    Сделать фото
                                </label>

                                <label onclick="sendMetric('reachGoal', 'download_user_photo_3');"  class="photo_btn" for="user_file_passport1">Загрузить фото</label>
                                <input type="file" id="user_file_passport1" name="passport1" accept="image/*" data-type="passport1" />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="passport4-file file-block">
                        
                        <legend>Фото карты</legend>
    
                        <div class="alert alert-danger " style="display:none"></div>
                        
                        <div class="user_files">
                            {if $passport4_file}
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="{$passport4_file->name|resize:100:100}" />
                                </div>
                                <span class="js-remove-file" data-id="{$passport4_file->id}">Удалить</span>
                                <input type="hidden" id="passport4" name="user_files[]" value="{$passport4_file->id}" />
                            </label>                                                
                            {/if}
                        </div>
                        
                        <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                            <div class="file-label">
                                <label for="user_file_passport4" class="file-label-image cvc-warning">
                                    <img src="design/{$settings->theme|escape}/img/card_logo.svg" />
                                    <i style="font-size: 9px;">Приложите фото Вашей именной банковской карты либо скриншот
                                        из личного кабинета банка, где видно полный номер и фамилия владельца.
                                        <strong>ВНИМАНИЕ! CVC-код нужно закрыть!</strong>
                                    </i>
                                </label>

                                <label onclick="sendMetric('reachGoal', 'get_user_photo_5');"  class="get_mobile_photo photo_btn not-visible-sm" for="user_file_passport4">
                                    Сделать фото
                                </label>

                                <label onclick="sendMetric('reachGoal', 'download_user_photo_5');"  class="photo_btn" for="user_file_passport4">Загрузить фото</label>
                                <input type="file" id="user_file_passport4" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                            </div>
                        </div>
                    </fieldset>
                    
                    <div id="other_files" >
                    {foreach $passport_files as $key => $user_file}
                        <fieldset class="user-file file-block">

                            <legend>Дополнительный файл</legend>

                            <div class="alert alert-danger " style="display:none"></div>

                            <div class="user_files">
                                {if $user_file}
                                <label class="file-label">
                                    <div class="file-label-image">
                                        <img src="{$user_file->name|resize:100:100}" />
                                    </div>
                                    <span class="js-remove-file" data-id="{$user_file->id}">Удалить</span>
                                    <input type="hidden" name="user_files[]" value="{$user_file->id}" />
                                </label>
                                {/if}
                            </div>

                            <div class="file-field" {if $user_file}style="display:none"{/if}>
                                <div class="file-label">
                                    <label for="user_file_user_file_{$key}" class="file-label-image"></label>

                                    <label onclick="sendMetric('reachGoal', 'get_user_photo_7');" for="user_file_user_file_{$key}"  class="get_mobile_photo photo_btn not-visible-sm">
                                        Сделать фото
                                    </label>

                                    <label onclick="sendMetric('reachGoal', 'download_user_photo_7');"  class="photo_btn" for="user_file_user_file_{$key}">Загрузить фото</label>
                                    <input type="file" id="user_file_user_file_{$key}" name="user_file" accept="image/jpeg,image/png" data-type="passport" />
                                </div>
                            </div>
                        </fieldset>
                    {/foreach}
                    
                    <fieldset id="new_other_file" class="user-file file-block">
                        
                        <legend>Дополнительный файл</legend>
    
                        <div class="alert alert-danger " style="display:none"></div>
                        
                        <div class="user_files">
                            
                        </div>
                        
                        <div class="file-field">
                            <div class="file-label">
                                <label for="user_file_user_file" class="file-label-image"></label>

                                <label onclick="sendMetric('reachGoal', 'get_user_photo_7');" for="user_file_user_file"  class="get_mobile_photo photo_btn not-visible-sm">
                                    Сделать фото
                                </label>

                                <label onclick="sendMetric('reachGoal', 'download_user_photo_7');"  class="photo_btn" for="user_file_user_file">Загрузить фото</label>
                                <input type="file" id="user_file_user_file" name="user_file" accept="image/jpeg,image/png" data-type="passport" />
                            </div>
                        </div>
                    </fieldset>
                    </div>
                    
                    <style>
                        @media (max-width: 768px) {
                            .mobile-green-bg {
                                background-color: #93cd52!important;
                            }
                        }
                    </style>
                    <div class="clearfix">
                        <input type="button" id="add_file" class="button button-inverse small" value="Добавить еще файл" />
                    </div>
                   <!-- <p
                        class="form-help mobile-green-bg"
                        style="font-weight: bold; text-align: left;"
                    >
                        Сделайте качественные фото, <span style="color: red;">и вероятность одобрения повысится!</span>
                        <ul
                            class="mobile-green-bg"
                            style="font-weight: bold; text-align: left;"
                        >
                            <li>располагайте документы так, чтобы они полностью помещались на фотографии;</li>
                            <li>текст должен быть читаемым и полностью виден;</li>
                            <li>исключите блики на фото.</li>
                            <li style="color: red;">Заём не выдаётся на Яндекс кошелек, Золотую корону, КИВИ и иные платежные системы.</li>
                            <li style="color: red;">Фото карты должны соответствовать прикрепленной на предыдущем этапе карты к платежной системе.</li>
                        </ul>
                    </p> -->
                    <p class="form-help">
                        * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                    </p>
                    <br />
                    <div class="clearfix next">
                        {if $is_developer}
                        <a class="button big button-inverse" id="" href="account?step=accept" >Назад</a>
                        {/if}
                        <input type="submit" name="confirm" class="button big" {if !$have_new_file}style="display:none"{/if} value="Далее" />
                    </div>
                    
                    
                </div>
            </form>
					
            
		</div>
	</div>
</section>

<div style="display:none">
    <div id="camera">
        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&#9421;</a>
        <div>
            <video id="video">Включите камеру и дайте разрешения для камеры повторно</video>
            <p class="text-red"></p>
        </div>
        <div class="camera_footer">
            <canvas id="canvas"></canvas>
            <div>
                <img src="" alt="Превью" id="photo" />
            </div>
            <button id="save_photo">Сохранить</button>
            <button id="get_photo">Сделать фото</button>
        </div>
    </div>
</div>


{* проверяем статус заявки через и аякс и если сменился перезагружаем страницу *}
{if $user_order && !$user_order->scorista_sms_sent}
<script type="text/javascript">
    $(function(){
        var _interval = setInterval(function(){
            $.ajax({
                url: 'ajax/check_1c_scorista.php',
                data: {
                    number: "{$user_order->id_1c}"
                },
                success: function(resp){
                    if (!!resp.sent)
                    {
                        _interval.clearInterval();
                    }
                }
            })
        }, 30000);
    })
</script>
{/if}

<script type="text/javascript">
    $(document).ready(function () {
        sendMetric('reachGoal', 'open_page_photo');
    });
</script>