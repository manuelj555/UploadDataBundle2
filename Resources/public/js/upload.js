/**
 * Created by Manuel Aguirre on 26/09/14.
 */
;
(function ($) {
    var $container = $('#upload-list-container');
    var url_list = $container.data('url');
    var $processing = $('<span class="label label-info"/>').html('<i class="glyphicon glyphicon-refresh"></i> Processing...');

    if ($container.size()) {
        window.reloadList = function reloadList() {
            $container.load(url_list);
        }

        setInterval(reloadList, 10000);
    }else{
        $container = $('body');
    }

    $container.on('click', '.upload-process', function (e) {
        e.preventDefault();
        var $a = $(this);
        var $row = $a.closest('.upload-row');
        $a.parent().html($processing.clone());
        $row.find('a.upload-process').addClass('disabled');
        console.log($row, $row.find('a'))
        $.ajax({
            url: $a.attr('href'),
            complete: function () {
                if($container.is(':not(body)')){
                    reloadList();
                }else{
                    $("#show-container").load($("#show-container").data('url'));
                }
            },
            error: function () {
                alert('Ups!!, Ocurrió un Error!!!');
            }
        });
    });
})(jQuery);