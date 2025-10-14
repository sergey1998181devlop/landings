function B2pApp()
{
    var app = this;
    
    _init_add_card = function(){
        $(document).on('click', '.js-b2p-add-card', function(e){
e.preventDefault();
            var $this = $(this);
            $.ajax({
                url: '/ajax/b2p_payment.php',
                data: {
                    action: 'attach_card'
                },
                success: function(resp){
                    if (!!resp.link)
                    {
                        location.href = resp.link;
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