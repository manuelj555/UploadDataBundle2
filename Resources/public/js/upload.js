/**
 * Created by Manuel Aguirre on 26/09/14.
 */
;
(function ($) {
    var $container = $('#upload-list-container');
    var url_list = $container.data('url');
    var $processing = $('<span class="label label-info"/>').html('<i class="glyphicon glyphicon-refresh"></i> Processing...');

    $container.on('click', '.upload-process', function (e) {
        e.preventDefault();
        var $a = $(this);
        $a.parent().html($processing.clone());
        $.ajax({
            url: $a.attr('href')
        });
    });

    setInterval(function () {
        $container.load(url_list);
    }, 5000);
})(jQuery);