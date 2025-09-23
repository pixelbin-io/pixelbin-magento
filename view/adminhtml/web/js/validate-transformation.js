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
            var regex = /^[a-zA-Z]\w*\.[a-zA-Z]\w*\((?:\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?)(?:,\s*\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?))*)?\)(?:~[a-zA-Z]\w*\.[a-zA-Z]\w*\((?:\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?)(?:,\s*\w+:([a-zA-Z0-9_\.\-]+|\[\[[^\]]+\]\]|true|false|-?\d+(?:\.\d+)?))*)?\))*$/;
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
