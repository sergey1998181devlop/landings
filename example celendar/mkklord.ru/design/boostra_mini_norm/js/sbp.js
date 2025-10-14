$('#close_sbp_block').click(function () {
    $('#block_sbp').hide();
    clearInterval(interval)
});

$('#attach_sbp').click(function() {
    $.ajax({
        url: 'ajax/b2p_payment.php',
        data: {
            action: 'attach_sbp',
        },
        success: function (resp) {
            if (resp.link) {
                window.open(resp.link, '_blank');
                return true;
            } else {
                return  false
            }
        }
    })
});