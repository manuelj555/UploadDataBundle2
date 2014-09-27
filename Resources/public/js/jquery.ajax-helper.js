/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
(function($) {

    $.fn.ajaxHelper = function(options) {

        var container = $(this);
        options = $.extend($.fn.ajaxHelper.defaults, options);

        $(container).on('click', '[data-ajax]:not(form,:input:not(:button, :submit,:reset))', function(event) {
            event.preventDefault();
            var $this = $(this);
            var url = $this.is('a') ? $this.attr('href') : $this.data('url');
            var container = $($this.data('ajax'));
            var before = window[$this.data('before')] ? window[$this.data('before')] : $.noop;
            var after = window[$this.data('after')] ? window[$this.data('after')] : $.noop;

            if (false === before($this, event, container)) {
                return;
            }

            var afterWrap = function(response) {

                if (container.size()) {
                    container.html(response);
                }

                after(response, $this, container);
            };

            $.get(url, afterWrap);

        }).on('submit', 'form[data-ajax]', function(event) {
            event.preventDefault();
            var $this = $(this);
            var url = $this.attr('action');
            var method = $this.attr('method');
            var container = $($this.data('ajax'));
            var before = window[$this.data('before')] ? window[$this.data('before')] : $.noop;
            var after = window[$this.data('after')] ? window[$this.data('after')] : $.noop;

            if (false === before($this, event, container)) {
                return;
            }

            var afterWrap = function(response) {
                if (container.size()) {
                    container.html(response);
                }

                after(response, $this, container);
            };

            $.ajax({
                url: url,
                data: $this.serialize(),
                type: method,
                success: afterWrap,
            });

        }).on('change', '[data-ajax]:input:not(:button, :submit,:reset)', function(event) {
            event.preventDefault();
            var $this = $(this);
            var url = $this.data('url');
            var container = $($this.data('ajax'));
            var before = window[$this.data('before')] ? window[$this.data('before')] : $.noop;
            var after = window[$this.data('after')] ? window[$this.data('after')] : $.noop;

            if (false === before($this, event, container)) {
                return;
            }

            var afterWrap = function(response) {

                if (container.size()) {
                    container.html(response);
                }

                after(response, $this, container);
            };

            $.get(url, {value: $this.val()}, afterWrap);
        }).on('click', '[data-ajax-container] a, [data-ajax-container] :button, [data-ajax-container] :submit, [data-ajax-container] :reset', function(event) {
            event.preventDefault();
            var $this = $(this);
            var $ajaxContainer = $this.closest('[data-ajax-container]');
            var url = $this.is('a') ? $this.attr('href') : $this.data('url');
            var container = $this.data('container') ? $($this.data('container')) : $($ajaxContainer.data('ajax-container'));

            if (window[$this.data('before')]) {
                var before = window[$this.data('before')];
            } else if (window[$ajaxContainer.data('before')]) {
                var before = window[$ajaxContainer.data('before')];
            } else {
                var before = $.noop;
            }

            if (window[$this.data('before')]) {
                var before = window[$this.data('before')];
            } else if (window[$ajaxContainer.data('before')]) {
                var before = window[$ajaxContainer.data('before')];
            } else {
                var before = $.noop;
            }

            if (window[$this.data('after')]) {
                var after = window[$this.data('after')];
            } else if (window[$ajaxContainer.data('after')]) {
                var after = window[$ajaxContainer.data('after')];
            } else {
                var after = $.noop;
            }

            if (false === before($this, event, container)) {
                return;
            }

            var afterWrap = function(response) {

                if (container.size()) {
                    container.html(response);
                }

                after(response, $this, container);
            };

            $.get(url, afterWrap);

        });


        $.ajaxSetup({
            complete: function(xhr) {
                var messages = $.parseJSON(xhr.getResponseHeader('X-Flashes'));

                if (!messages) {
                    return;
                }

                console.debug(messages);

                var preparedMessages = [];

                $.each(messages, function(key, value) {
                    if ($.isArray(this)) {
                        var type = this[0];
                        var text = this[1];
                    } else {
                        var type = 'default';
                        var text = value;
                    }

                    if (preparedMessages[type]) {
                        preparedMessages[type].push(text);
                    } else {
                        preparedMessages[type] = [text];
                    }
                });

                options.flashCallback(preparedMessages, messages);

            },
        });
    };

    $.fn.ajaxHelper.defaults = {
        flashCallback: $.noop
    };

})(jQuery);


