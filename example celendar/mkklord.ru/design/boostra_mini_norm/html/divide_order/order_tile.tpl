<div class="main-tile-div tile-order-{$order_data->order->order_id}">
    <p>Заём
        {if $order_data->order->organization_id == 6}
        Аквариус
        {else}
            {$config->org_name}
        {/if}
        {$order_data->balance->zaim_number}
    </p>

    <button class="main-tile-div-button" data-order = {$order_data->order->order_id}>Открыть</button>
</div>

<div class="hidden-content-{$order_data->order->order_id} order-data" style="display: none;">
    {include file='divide_order/balance.tpl' order_data_index=$order_data@index}
</div>

<style>
    .main-tile-div{
        width: 30%;
        border: 2px solid;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-direction: column;
        min-width: 300px;
        padding: 5px 0 20px;
        margin: 20px 0!important;
        height: 150px;
        box-sizing: border-box;
        order: 1000;
    }
    .main-tile-div>p,.contracts-list>p{
        margin: 5px !important;
    }
    .main-tile-div-button{
        width: 70%;
        min-width: 100px;
        background: white !important;
        color: black !important;
        border: 1px solid;
        padding: 20px 0;
    }
    .main-tile-div-button:hover {
        background: black !important;
        color: white !important;
    }
    .order-data{
        display: none;
    }
    .contracts-list{
        width: 30%;
        min-width: 300px;
        background: white !important;
        color: black !important;
        border: 1px solid;
        justify-content: center;
        align-items: center;
        border-radius: 20px;
        gap: 10px;
        display: none;
        margin-bottom: 20px;
        cursor: pointer;
    }
    .contracts-main-tile-div{
        display: flex;
        flex-direction: column
    }
</style>

<script>
    let loanCounter = 0
    $(document).on('click','.main-tile-div-button',function (){
        let id = $(this).data('order')
        loanCounter++
        $('.hidden-content-'+ id).css({
            'display':'block',
            'order': loanCounter
                })
        $('.tile-order-'+ id).css('display','none')
        $('.contracts-list').css('display','flex')
    })

    $(document).on('click','.contracts-list',function (){
        $('.main-tile-div').css('display','flex')
        $('.order-data').css('display','none')
        $(this).css('display','none')
    })

</script>