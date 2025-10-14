{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{assign var="access_modified_file" value="{empty(count($user->loan_history)) && !in_array($last_order['1c_status'], ['3.Одобрено', '5.Выдан'])}"}

{literal}
<style>
    #img_modal {
        width: 1200px;
        max-width: 90%;
    }
    #img_modal img {
        max-width: 100%;
        height: auto;
    }
    #img_modal .close {
        text-align: right;
    }
    #img_wrapper {
        width: 100%;
        text-align: center;
    }
</style>
<script>

function UploadApp()
{
    let app = this;
    const access_modified_file = "{/literal}{$access_modified_file|escape:'javascript'}{literal}";

    app.init = function(){
        $(document).on('change', '[type=file]', function(){
            app.upload(this);
        });
        
        $(document).on('click', '.remove-file', function(e){
            e.preventDefault();

            if (1 || confirm('Вы хотите удалить файл? Операцию не возможно будет отменить.'))
            {
                app.remove($(this));
            }
            return false;
        });

        $(document).on('click', '.user_files img', function (e) {
            let img = document.createElement('img');
            img.src = $(this).data('original');
            $("#img_wrapper").html(img);

            $.magnificPopup.open({
                items: {
                    src: '#img_modal'
                },
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        });
    };
    
    app.remove = function($this){
        
        var file_id = $this.data('id');
        var $fileblock = $this.closest('.file-block');

        $.ajax({
            url: 'ajax/upload.php',
            data: {
                id: file_id,
                action: 'remove'
            },
            type: 'POST',
            dataType: 'json',
            beforeLoad: function(){

            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    $this.closest('.user_file').fadeOut();
                    
                    $fileblock.find('.file-field').fadeIn();
                }
            }
        });
        
        $.ajax({
            url: 'ajax/upload.php',
            data: {
                id: file_id,
                action: 'remove'
            },
            type: 'POST',
            dataType: 'json',
            beforeLoad: function(){

            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    $this.closest('.user_file').fadeOut();
                    
                    $fileblock.find('.file-field').fadeIn();
                }
            }
        });
        
    };
    
    app.upload = function(input) {
        $('#files_form').addClass('loading');

        let self = $(input),
            fileBlock = self.closest('.file-block'),
            _type = self.data('type'),
            form_data = new FormData();

        fileBlock = fileBlock.length !== 0 ? fileBlock : $('.passport-files');

        form_data.append('file', input.files[0])
        form_data.append('type', _type);
        form_data.append('action', 'add');

        $.ajax({
            url        : 'ajax/upload.php',
            data       : form_data,
            type       : 'POST',
            dataType   : 'json',
            processData: false,
            contentType: false,
            beforeLoad : function () {
                fileBlock.addClass('loading');
            },
            success    : function (resp) {
                if (!!resp.error) {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else if (resp.error == 'extension')
                        error_text = 'Файл не удалось загрузить, Недопустимое расширение файла. Допускается загрузка форматов: ' + resp.allowed_extensions.join(', ');
                    else
                        error_text = resp.error;

                    fileBlock.find('.alert').html(error_text).fadeIn();
                } else {
                    fileBlock.find('.alert').fadeOut();

                    var _preview = '';

                    _preview += '<div class="user_file">';
                    _preview += '<div class="image-status uploaded" title="Файл загружен, отправьте его на проверку. Не отправленные файлы, через 5 дней после загрузки, будут удалены"><span></span></div>';
                    _preview += '<img src="' + resp.filename + '" data-original="{/literal}{$config->root_url}/{$config->original_images_dir}{literal}' + resp.name + '" />';

                    if (resp.type === 'passport' && access_modified_file) {
                        _preview += '<a href="javascript:void(0);" class="remove-file" data-id="'+resp.id+'">&times;</a>';
                    }

                    _preview += '</div>';

                    fileBlock.find('.user_files').append(_preview);

                    if (_type != 'passport')
                        fileBlock.find('.file-field').fadeOut();

                    self.closest('form').find('[name=confirm]').fadeIn();

                    if (self.hasClass('js-replace')) {
                        var _id = self.data('replace');
                        $.ajax({
                            url       : 'ajax/upload.php',
                            data      : {
                                id    : _id,
                                action: 'remove'
                            },
                            type      : 'POST',
                            dataType  : 'json',
                            beforeLoad: function () {

                            },
                            success   : function (resp) {
                                if (resp.error) {
                                    alert(resp.error);
                                    return;
                                }
                                fileBlock.find('.alert').fadeOut();

                                $('#file_' + _id).fadeOut();
                                fileBlock.find('.js-replace-block').remove();
                            }
                        });
                    }
                }
            }
        }).done(function () {
            $('#files_form').removeClass('loading');
        });
    }
    
    ;(function(){
        app.init();
    })();
};
new UploadApp();

</script>
{/literal}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

    		{include file='user_nav.tpl' current='upload'}

			<div class="content">
				<div class="panel">
                    
                    <form id="files_form" method="POST" enctype="multipart/form-data" >
                        
                        {if $error=='error_upload'}
                        <div class="alert alert-danger">
                            При передаче файлов произошла ошибка, попробуйте повторить позже.
                        </div>
                        {/if}
                        <fieldset class="passport1-file file-block" style="background:url('design/{$settings->theme|escape}/img/passport1.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Разворот главной страницы паспорта (2-3 стр.)</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport1_file}
                                <div class="user_file" id="file_{$passport1_file->id}">
                                    {if $passport1_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $passport1_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $passport1_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $passport1_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$passport1_file->id}" />
                                    <img src="{$passport1_file->name|resize:100:100}" data-original="{$config->root_url}/{$config->original_images_dir}{$passport1_file->name}" />
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="passport1" accept="image/jpeg,image/png" data-type="passport1" />
                                </label>
                            </div>
                            
                            {if $passport1_file->status == 3 || $access_modified_file}
                                <div class="file-field js-replace-block">
                                    {if $passport4_file->status == 3}
                                        <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                    {/if}
                                    <label class="file-label">
                                        <span id="passport1_list">Заменить файл</span>
                                        <input type="file" name="passport1_replace" class="js-replace" accept="image/jpeg,image/png" data-type="passport1" data-replace="{$passport1_file->id}" />
                                    </label>
                                </div>
                            {/if}
                            
                        </fieldset>
                        <fieldset class="passport4-file file-block" style="background:url('design/{$settings->theme|escape}/img/card_logo.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Фото карты</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport4_file}
                                    <div class="user_file" id="file_{$passport4_file->id}">
                                        {if $passport4_file->status == 0}
                                            <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span>
                                            </div>
                                        {elseif $passport4_file->status == 1}
                                            <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                        {elseif $passport4_file->status == 2}
                                            <div class="image-status accept" title="Файл принят"><span></span></div>
                                        {elseif $passport4_file->status == 3}
                                            <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                        {/if}
                                        <input type="hidden" name="user_files[]" value="{$passport4_file->id}"/>
                                        <img src="{$passport4_file->name|resize:100:100}"
                                             data-original="{$config->root_url}/{$config->original_images_dir}{$passport4_file->name}"/>
                                    </div>

                                    {if $passport4_file->status == 3 || $access_modified_file}
                                        <div class="file-field js-replace-block">
                                            {if $passport4_file->status == 3}
                                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                            {/if}
                                            <label class="file-label">
                                                <span id="passport4_list">Заменить файл</span>
                                                <input type="file" name="passport4_replace" class="js-replace" accept="image/jpeg,image/png" data-type="passport4" data-replace="{$passport4_file->id}" />
                                            </label>
                                        </div>
                                    {/if}
                                {/if}
                            </div>

                            <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                                </label>
                            </div>
                        </fieldset>
                        <fieldset class="passport-files file-block width100">
                            
                            <legend>Дополнительные фото</legend>

                            <div class="alert alert-danger" style="display:none"></div>
                            
                            <div class="user_files">
                            {foreach $passport_files as $pfile}
                                <div class="user_file" id="file_{$pfile->id}">
                                    {if $pfile->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $pfile->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $pfile->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $pfile->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$pfile->id}" />
                                    <img src="{$pfile->name|resize:100:100}" data-original="{$config->root_url}/{$config->original_images_dir}{$pfile->name}" />
                                    {if $access_modified_file}
                                        <a href="javascript:void(0);" class="remove-file" data-id="{$pfile->id}">&times;</a>
                                    {/if}
                                </div>
                            {/foreach}
                            </div>
                            
                            <div class="file-field passport-field" {if ($passport_files|count) > 20}style="display:none"{/if}>

                                <label class="file-label passport-label">
                                    <span>Добавить Файл</span>
                                    <input type="file" class="passport-input" name="passport[]" accept="image/jpeg,image/png" data-type="passport" />
                                </label>
                            </div>
                            
                        </fieldset>

                        {if $has_rejected_photo}
                            <input type="submit" name="confirm" class="button medium"  value="Отправить файлы на проверку" />
                        {/if}

                        <style>
                            @media (max-width: 768px) {
                                .mobile-green-bg {
                                    background-color: #93cd52 !important;
                                }
                            }
                        </style>
                        <div class="clearfix">
                            <label class="add_file-label" for="add_file">Добавить еще файл</label>
                            <input id="add_file"
                                   name="passport[]"
                                   class="button button-inverse small"
                                   type="file"
                                   style="display: none;"
                                   accept="image/jpeg,image/png"
                                   data-type="passport"
                                   value="Добавить еще файл"/>
                        </div>
                        <p
                                class="form-help mobile-green-bg"
                                style="font-weight: bold; text-align: left;"
                        >
                            Сделайте качественные фото, <span
                                    style="color: red;">и вероятность одобрения повысится!</span>
                        <ul
                                class="mobile-green-bg"
                                style="font-weight: bold; text-align: left;"
                        >
                            <li>располагайте документы так, чтобы они полностью помещались на фотографии;</li>
                            <li>текст должен быть читаемым и полностью виден;</li>
                            <li>исключите блики на фото.</li>
                        </ul>
                        </p>
                        <p class="form-help">
                            * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                        </p>
                        <br/>
                        <div class="clearfix next">
                            {if $is_developer}
                                <a class="button big button-inverse" id="" href="account?step=additional">Назад</a>
                            {/if}
                            <input type="submit" name="confirm" class="button big"
                                   {if !$have_new_file}style="display:none"{/if} value="Далее"/>
                        </div>
                        
                    </form>
					
				</div>
				
			</div>
		</div>
	</div>
    <div id="img_modal" class="white-popup-modal mfp-hide">
        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&#9421;</a>
        <div class="modal-content">
            <div id="img_wrapper"></div>
        </div>
    </div>
</section>