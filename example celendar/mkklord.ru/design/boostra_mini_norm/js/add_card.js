$(document).ready(function () {

    let $modal = $("#myModal");
    let $btn = $("#myBtn, #js-assign-old-card-btn");

    let $toggle = $('.js-toggle-cards');
    let $cards = $('.js-cards');
    
    $toggle.click(function(e){
        e.preventDefault();
        $cards.slideToggle();
    });
    
    $btn.on("click", function () {
        localStorage.openCardModal = false

        var $this = $(this);
        var organization_id = $(this).data('organization_id') || 1;

        $.ajax({
            url: 'ajax/b2p_payment.php',
            data: {
                action: 'attach_card',
                organization_id: organization_id,
            },
            success: function (resp) {
                if (!!resp.link) {
                    location.href = resp.link;
                    return true;
                } else {
                    e.preventDefault();
                }
            }
        })
    });


    if (localStorage.openCardModal == 'true') {
        $modal.css("display", "block");
    }

    $('.next-step-button').click(function () {
        if ($('#user_file_passport4').closest('.file-block').find('.alert').html() == ""){
            $modal.css("display", "none");
            $.magnificPopup.open({
                items: {
                    src: '#modal_connect'
                },
                closeOnBgClick: false,
                type: 'inline',

            });
            delete localStorage.openCardModal

        }
    })


    $('.finish_step').click(function () {
        $.magnificPopup.close()
        delete localStorage.cardPan
    })
});