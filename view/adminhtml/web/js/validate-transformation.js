require([
    'jquery',
    'mage/translate',
    'jquery/validate'
], function ($, $t) {
    'use strict';

    $.validator.addMethod(
        "validate-transformation",
        function (value, element) {
            if (value.trim() === '') {
                return true; // allow empty
            }
            var regex = /^(?:[A-Za-z]+\.[A-Za-z]+\(\s*(?:[A-Za-z]+:(?:\d{1,3}|"[^"]*")(?:\s*,\s*[A-Za-z]+:(?:\d{1,3}|"[^"]*"))*)?\s*\))(?:\s*~\s*[A-Za-z]+\.[A-Za-z]+\(\s*(?:[A-Za-z]+:(?:\d{1,3}|"[^"]*")(?:\s*,\s*[A-Za-z]+:(?:\d{1,3}|"[^"]*"))*)?\s*\))*$/;
            return regex.test(value);
        },
        $t("Please enter a valid transformation")
    );

    $(function () {
        // Global Custom Transformation field
        $('input[name="groups[image_transformations][fields][global_custom_transformation][value]"]')
            .addClass('validate-transformation');

        // Product Custom Transformation field
        $('input[name="groups[image_transformations][fields][product_custom_transformation][value]"]')
            .addClass('validate-transformation');
    });
});
