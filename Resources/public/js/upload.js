/**
 * Created by Manuel Aguirre on 26/09/14.
 */
var UploadData = function (opts) {
    var $ = jQuery;
    var $processing = $('<span class="label label-warning"/>').html('<i class="glyphicon glyphicon-refresh"></i>...');

    var options = {
        container: $('body'),
        url_refresh: null,
        refresh_complete: function () {
        },
        error: function () {
            alert('Ups!!, Ocurrió un Error!!!');
        },
        auto_reload: false,
        auto_reload_time: 10000
    };

    options = $.extend(options, opts);

    this.reload = function () {
        options.container.load(options.url_refresh, options.refresh_complete);
    };

    if (options.auto_reload) {
        setInterval(this.reload, options.auto_reload_time);
    }

    var upload = this;

    options.container.on('click', '.upload-process', function (e) {
            e.preventDefault();
            var $a = $(this);
            var $row = $a.closest('.upload-row');

            if (!$a.is('[data-modal]')) {
                $a.parent().html($processing.clone());
                $row.find('a.upload-process').addClass('disabled');
            }
            $.ajax({
                url: $a.attr('href'),
                success: function (content) {
                    if ($a.is('[data-modal]')) {
                        $('#upload-ajax-extra-content').html(content);
                    } else {
                        upload.reload();
                    }
                },
                error: function () {
                    options.error();
                }
            });
        }
    );

    $.ajaxSetup({
        complete: function (xhr) {
            if (xhr.getResponseHeader('X-Reload')) {
                upload.reload();
            }
        }
    });

    $('body').append('<div id="upload-ajax-extra-content" />');
};