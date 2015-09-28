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
            if(confirm(text)){
                callback();
            }
        },
        filter_form: $('.upload_filter_form')
    };

    options = $.extend(options, opts);

    this.reload = function () {
        $.get(options.url_refresh).done(function(html){
            $(options.reload_container_selector).html($(html).find(options.reload_container_selector).html());
        }).done(options.refresh_complete);
    };

    if (options.auto_reload) {
        setInterval(this.reload, options.auto_reload);
    }

    var upload = this;

    options.container.on('click', '.upload-process', function (e) {
            e.preventDefault();
            var $a = $(this);
            var $row = $a.closest('.upload-row');

            function processClick(){
                if (!$a.is('[data-modal]')) {
                    $a.parent().html($processing.clone());
                    $row.find('a.upload-process').addClass('disabled');
                }
                $.ajax({
                    url: $a.attr('href'),
                    data: options.filter_form.serializeArray(),
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

            if ($a.is('[data-confirm]')) {
                options.confirm($a.data('confirm'), processClick);
            }else{
                processClick();
            }
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
