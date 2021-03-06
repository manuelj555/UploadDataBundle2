;(function ($) {

    $.fn.lyUploadMatcherValidate = function () {

        function validateColumnsForm($form) {
            var $selectHeaders = $form.find("select.file-header").removeClass('with-errors').toArray();

            for (var sh in $selectHeaders) {
                var $current = $form.find($selectHeaders[sh]);

                if (!$current.val() && $current.is['[required]']) {
                    $current.addClass('with-errors');

                    return "required";
                }

                var $otherSelects = $form.find("select.file-header").not($current).toArray();

                for (var sh2 in $otherSelects) {
                    if ($current.val() && $form.find($otherSelects[sh2]).val() == $current.val()) {
                        $form.find($otherSelects[sh2]).addClass('with-errors').focus();

                        return "repeated";
                    }
                }
            }

            return true;
        }

        return validateColumnsForm($(this));
    };
})(jQuery);
