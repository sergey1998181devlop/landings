function B2pApp()
{
    var app = this;
    
    _init_add_card = function(){
        $(document).on('click', '.js-b2p-add-card', function(e){
            localStorage.openCardModal = false
e.preventDefault();
            var $this = $(this);
            var organization_id = $(this).data('organization_id') || 1;
            $(this).hide();
            $('.security-text').hide();
            $('.top_menu__logo').hide();
            $.ajax({
                url: '/ajax/b2p_payment.php',
                data: {
                    action: 'attach_card',
                    organization_id: organization_id,
                },
                success: function(resp){
                    if (!!resp.link)
                    {
                        const iframe = document.getElementById('add_card_frame');
                        $('#add_card_frame').attr('src', resp.link);
                        iframe.style.display = 'block';
                        iframe.addEventListener('load', function () {
                            try {
                                const currentURL = iframe.contentWindow.location.href;
                                console.log('Текущий URL дочернего фрейма:', currentURL);
                                location.reload();
                            } catch (e) {
                                console.error('Не удалось получить URL дочернего фрейма:', e);
                            }
                        });
                        //location.href = resp.link;
                        return true;
                    }
                    else
                    {
                        e.preventDefault();
                    }
                }
            })
        })
    };
    
    ;(function(){
        _init_add_card();
    })();
}
$(function(){
    new B2pApp();
})