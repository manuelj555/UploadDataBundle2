/**
 * Created by Manuel Aguirre on 26/09/14.
 */
var UploadData = function (opts) {
    var $ = jQuery;
    var $processing = $('<span class="label label-warning"/>').html('<i class="glyphicon glyphicon-refresh"></i>...');

    var options = {
        container: $('body'),
        reload_container_selector: 'body',
        url_refresh: null,
        refresh_complete: function () {
        },
        error: function () {
            alert('Ups!!, Ocurrió un Error!!!');
        },
        auto_reload: false,
        confirm: function (text, callback) {
            if (confirm(text)) {
                callback();
            }
        },
        filter_form: $('.upload_filter_form')
    };

    options = $.extend(options, opts);

    this.reload = function () {
        $.get(options.url_refresh).done(function (html) {
            $(options.reload_container_selector).html($(html).find(options.reload_container_selector).html());
        }).done(options.refresh_complete);
    };

    if (options.auto_reload) {
        setInterval(this.reload, options.auto_reload);
    }

    var upload = this;

    options.container.on('click', '.upload-process[data-modal]', function (e) {
        e.preventDefault();
        var $a = $(this);
        var $row = $a.closest('.upload-row');

        $.ajax({
            url: $a.attr('href'),
            data: options.filter_form.serializeArray(),
            success: function (content) {
                $('#upload-ajax-extra-content').html(content);
            },
            error: function () {
                options.error();
            }
        });
    }).on('click', '.upload-process[data-confirm]', function (e) {
        e.preventDefault();
        var $a = $(this);

        options.confirm($a.data('confirm'), function () {
            document.location = $a.attr('href')
        });
    });

    $.ajaxSetup({
        complete: function (xhr) {
            if (xhr.getResponseHeader('X-Reload')) {
                upload.reload();
            }
        }
    });

    $('body').append('<div id="upload-ajax-extra-content" />');
};