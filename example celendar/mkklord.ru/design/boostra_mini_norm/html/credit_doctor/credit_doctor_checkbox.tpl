{if !$notOverdueLoan}
        {assign var="shouldChecked" value=!$returnSafetyFlowCreditDoctor}

        {if $applied_promocode->disable_additional_services || (!empty($last_order_data) && isset($last_order_data['disable_additional_service_on_issue']) && $last_order_data['disable_additional_service_on_issue'] == 1)}
            <input type="hidden" value="0" id="credit_doctor_check{$idkey}" name="credit_doctor_check"/>
        {else}
            {if !($user_return_credit_doctor)}
                <div id="credit_doctor_check_wrapper" style="{if $shouldChecked}display:none;{else}{/if}">
                    <label class="spec_size">
                        <div class="checkbox"
                             style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                            <input type="checkbox" value="1" id="credit_doctor_check{$idkey}" name="credit_doctor_check"
                                   {if $shouldChecked}checked{/if}/>
                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                        </div>
                    </label>
                    <p>
                        Настоящим подтверждаю, что полностью ознакомлен и согласен с приобретением ПО «Финансовый
                        доктор»
                        СВФСИС №20156 стоимостью
                        {if $currentPage == 'account_additional_data'}
                            <span class="credit_doctor_amount_strong">{$credit_doctor_amount}</span>
                        {else}
                            <span class="credit_doctor_amount"></span>
                            <input type="hidden" id="hidden_amount_calc_one" value="{$credit_doctor_amount}">
                        {/if}
                        рублей,
                        предоставляемой в соответствии с <a href="user/docs?action=additional_service"
                                                            target="_blank">заявлением
                            о предоставлении дополнительных продуктов</a> и
                        <a href="/files/doc/dogovor-oferta-cd.pdf" target="_blank">офертой</a>.
                        <a type="button" class="btn btn-prolongation" id="btn-modal-creditdoctor">Подробнее</a>
                    </p>
                </div>
            {else}
                <a id="btn-modal-creditdoctor" style="display: none"></a>
                <div id="creditDoctorData" data-utm-source="{$user->utm_source}"></div>
                <input type="checkbox" value="1" id="credit_doctor_check{$idkey}" name="credit_doctor_check"
                       style="display: none" checked/>
            {/if}
        {/if}
        <script>
            $(document).ready(function () {
                function updateHiddenField() {
                    if ($('#credit_doctor_check{$idkey}').is(':checked')) {
                        $('#credit_doctor_hidden{$idkey}').val('1');
                    } else {
                        $('#credit_doctor_hidden{$idkey}').val('0');
                    }
                }

                updateHiddenField();

                $('#credit_doctor_check{$idkey}').on('change', function () {
                    updateHiddenField();
                });

                if ($('#money-edit').length || $('input[name="insure"]').length) {
                    $('.credit_doctor_amount').text($('#hidden_amount_calc_one').val());
                }
            });
        </script>
{/if}