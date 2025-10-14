{* Страница инициализации пользователя *}

{* Канонический адрес страницы *}
{$canonical="/init_user" scope=parent}

{$meta_title = "Проверка логина" scope=parent}
<style>
    .animate-blink {
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 100% {
            -webkit-box-shadow:0px 0px 15px 5px rgb(9 151 255);
            -moz-box-shadow: 0px 0px 15px 5px rgb(9 151 255);
            box-shadow: 0px 0px 15px 5px rgb(9 151 255);
        }

        50% {
            -webkit-box-shadow:0px 0px 0px 0px rgba(56,61,156,0);
            -moz-box-shadow: 0px 0px 0px 0px rgba(56,61,156,0);
            box-shadow: 0px 0px 0px 0px rgba(56,61,156,0);
        }
    }

    .auth-button-tinkoff {
        display: block;
        margin-top: 20px;
        background-color: #FFDD2D;
        color: black;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }

    #agree {
        width: 30px;
        height: 30px;
    }

    .agree-label {
        margin-top: 6px;
        margin-left: 10px;
        font-size: 17px;
    }

    @media(max-width: 767px) {
        #agree {
            width: 23px;
            height: 23px;
        }

        .agree-label {
            margin-top: 0px;
            font-size: 12px;
        }
    }
</style>

<section id="init_user">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <h4 class="mb-3">Подтвердите номер телефона для продолжения регистрации.</h4>
            <form class="position-relative" id="init_user-form">
                <input name="check_user" value="1" type="hidden" />
                <input name="huid" type="hidden" value="{$settings->hui}" />
                <div class="mb-3">
                    <label for="phone" class="form-label">Номер телефона</label>
                    <input type="text" name="phone" class="form-control" id="phone" placeholder="+7-777-777-77-77" {if isset($user_phone) && $user_phone != ''} value="{$user_phone|escape}" {/if} />
                </div>
                <div class="my-2 text-danger" id="phone-error" style="display: none"><small>Код оператора введен не верно.</small></div>

                <div class="form-check my-2">
                    <input class="form-check-input" type="checkbox" id="agree" name="agree">
                    <label class="form-check-label agree-label" for="agree">
                        Нажимая "Продолжить", я соглашаюсь со
                        <a href="#" data-bs-toggle="collapse" data-bs-target="#documentsCollapse" aria-expanded="false" aria-controls="documentsCollapse">
                            следующими условиями
                        </a>
                    </label>
                </div>

                <!-- Скрытый по умолчанию список условий -->
                <div class="collapse mt-2" id="documentsCollapse">
                    {include 'registration_user_doc_list.tpl'}
                </div>

                <button type="submit" class="validate-btn w-100 btn btn-primary">Продолжить</button>
                {if empty($user_phone) && isset($smarty.get.tid)}
                    <a href="/ajax/auth/" id="auth-button-tinkoff" class="auth-button-tinkoff">Войти с Tinkoff ID</a>
                    <input name="huid" type="hidden" value="{$authUrl}" />
                {/if}
                <div id="smart-captcha-loan-container" style="display: none;" class="smart-captcha mt-3" data-sitekey="{$config->smart_captcha_client_key}"></div>
            </form>
            <form class="position-relative" id="init_user-check_sms_form" style="display: none">
                <h5 id="init_user-phone_title" class="text-primary my-3"></h5>
                <input name="huid" type="hidden" value="{$settings->hui}" />
                <div class="input-group mb-3">
                    <span class="input-group-text">Введите код</span>
                    <input type="text" name="code" class="form-control" id="code" placeholder="****" />
                </div>
                <button type="submit" class="w-100 btn btn-primary">Отправить повторно <i class="mx-2 bi bi-repeat"></i></button>
            </form>
        </div>
    </div>
</section>

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.00" type="text/javascript"></script>
    <script type="text/javascript">
        const stateInitUser = {
            inputPhone: false,
        };
        function initSmartCaptcha (addAnimation = false) {
            if (window.smartCaptcha) {
                const container = document.getElementById('smart-captcha-loan-container');
                const widgetSmartCaptchaId = window.smartCaptcha.render(container, {
                    sitekey: container.dataset.sitekey,
                    hl: 'ru',
                });

                if (addAnimation && typeof addAnimation === 'boolean') {
                    $(container).show().addClass('animate-blink');
                } else {
                    $(container).show().removeClass('animate-blink');
                }
            }
        }

        function checkSmsInitUser(code, phone) {
            $('#init_user .alert').remove();
            $("#init_user-check_sms_form input[name='code']").removeClass('is-valid').removeClass('is-invalid');

            $.ajax({
                url: 'ajax/sms.php',
                data: {
                    phone,
                    code,
                    action: 'check_init_user',
                    calc_amount: {$calc_amount},
                    calc_period: {$calc_period}.
                },
                method: 'GET',
                beforeSend: function () {
                    $("#init_user-check_sms_form").addClass('loading');
                    $("#init_user-check_sms_form button").prop('disabled', true);
                },
                success: function (resp) {
                    if (resp.success && resp.redirect_url) {
                        $('#init_user').prepend('<div class="alert alert-success" role="alert">Происходит переход на следующую страницу</div>');
                        $("#init_user-check_sms_form input[name='code']").addClass('is-valid');

                        if (resp.is_new_user == 1) {
                            sendMetric('reachGoal', 'podtverdil_telefon');
                        } else if (resp.is_new_user == 0) {
                            sendMetric('reachGoal', 'pk_podtverjdenienomera');
                        }

                        window.location = resp.redirect_url;
                    } else {
                        $("#init_user-check_sms_form input[name='code']").addClass('is-invalid');
                        $('#init_user').prepend('<div class="alert alert-danger" role="alert">Код введен не верно.</div>');
                        $("#init_user-check_sms_form").removeClass('loading');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $("#init_user-check_sms_form button").prop('disabled', false);
            });
        }

        function sentInitSms(phone) {
            $('#init_user .alert').remove();
            let postData = $("#init_user-check_sms_form").serializeArray();
            postData.push({
                name: 'flag',
                value: 'LOGIN'
            },{
                name: 'phone',
                value: phone
            }, {
                name: 'page',
                value: 'init_user'
            });

            $.ajax({
                url: 'ajax/send_sms.php',
                data: postData,
                method: 'POST',
                beforeSend: function () {
                    $("#init_user-check_sms_form").addClass('loading');
                    $("#init_user-check_sms_form button").prop('disabled', true);
                },
                success: function (resp) {
                    if (!!resp.captcha) {
                        switch (resp.captcha) {
                            case 'init':
                                $("#init_user-check_sms_form").append($("#init_user-form #smart-captcha-loan-container").clone(true))
                                $("#init_user-form #smart-captcha-loan-container").remove();
                                initSmartCaptcha();
                                break;
                            case 're_init':
                                initSmartCaptcha();
                                break;
                            case 'empty_token':
                                $('#init_user').prepend('<div class="alert alert-danger" role="alert">Проверка не пройдена</div>');
                                break;
                        }

                        $("#init_user-phone_title").html("<span class='text-danger'>Пройдите проверку капчи</span>");
                    } else {
                        $("#init_user-phone_title").html("На Ваш номер <b>" + phone + "</b> был отправлен код <b>№" + resp.number_sms + "</b> подтверждения.");

                        $('#smart-captcha-loan-container').removeClass('animate-blink');
                        if (!!resp.time_error) {
                            $('#init_user').prepend('<div class="alert alert-danger" role="alert">' + resp.time_error + '</div>');
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $("#init_user-check_sms_form").removeClass('loading');
                $("#init_user-check_sms_form button").prop('disabled', false);
            });
        }

        $(document).ready(function () {
            const scriptElement = document.createElement('script');
            scriptElement.src = 'https://smartcaptcha.yandexcloud.net/captcha.js?render=onload';
            // scriptElement.onload = initSmartCaptcha;
            scriptElement.onerror = function (error) {
                console.log('Error Smart captcha script error: ', error);
            };
            document.body.appendChild(scriptElement);

            $("#init_user-check_sms_form").on('submit', function (e) {
                e.preventDefault();
                sentInitSms($("#init_user-form input[name='phone']").val());
            })

            $("#init_user-check_sms_form input[name='code']").inputmask({
                mask: "9999",
                clearIncomplete: true,
                oncomplete: function () {
                    checkSmsInitUser($(this).val(), $("#init_user-form input[name='phone']").val());
                },
            });

            $("#init_user-form input[name='phone']").inputmask({
                mask: "+7 (999) 999-99-99",
                clearIncomplete: true,
                oncomplete: function () {
                    $(this).removeClass('is-valid').removeClass('is-invalid');
                    if ($(this).valid() && !$("#phone-error").is(':visible')) {
                        $(this).addClass('is-valid');
                        if (!stateInitUser.inputPhone) {
                            sendMetric('reachGoal', 'vvel_telefon');
                            stateInitUser.inputPhone = true;
                        }
                    } else {
                        $(this).addClass('is-invalid');
                    }
                },
            });

            $("#init_user-form input[name='agree']").on("change", function () {
                $(this).removeClass('is-valid').removeClass('is-invalid')
                if ($(this).prop('checked')) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).addClass('is-invalid');
                }
            });

            $("#init_user-form").on('submit', function (e) {
                e.preventDefault();
                let errors = [],
                    phone  = $("#init_user-form input[name='phone']").val();

                $("#init_user-form input").removeClass('is-invalid');
                $('#init_user .alert').remove();

                if (!$("#init_user-form input[name='phone']").inputmask("isComplete") && !$("#phone-error").is(':visible')) {
                    $("#init_user-form input[name='phone']").addClass('is-invalid');
                    errors.push('phone')
                }

                if (!$("#init_user-form input[name='agree']").prop('checked')) {
                    $("#init_user-form input[name='agree']").addClass('is-invalid');
                    errors.push('agree')
                }

                if (!!errors.length) {
                    return;
                }

                $.ajax({
                    url: 'ajax/loginCodeByCall.php',
                    data: $(this).serializeArray(),
                    method: 'POST',
                    beforeSend: function () {
                        $("#init_user-form").addClass('loading');
                        $("#init_user-form button").prop('disabled', true);
                    },
                    success: function (resp) {
                        if (resp.error && resp?.error_type !== 'user_not_find') {
                            let errorMessage = resp.error;
                            switch (resp.error) {
                                case 'user_blocked':
                                    errorMessage = 'Пользователь заблокирован';
                                    break;
                            }
                            $('#init_user').prepend('<div class="alert alert-danger" role="alert">' + errorMessage + '</div>');
                        } else if (!!resp.captcha_error) {
                            initSmartCaptcha(true)
                        } else {
                            $("#init_user-form").hide();
                            $("#init_user-check_sms_form").fadeIn();
                            sentInitSms(phone)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                        alert(error);
                        console.log(error);
                    },
                }).done(function () {
                    $("#init_user-form").removeClass('loading');
                    $("#init_user-form button").prop('disabled', false);
                });
            });
        });
        $(document).ready(function () {
            sendMetric('reachGoal', 'sglavnoynatel')
        });
    </script>
{/capture}
