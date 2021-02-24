(function ($) {
    'use strict';
    Drupal.behaviors.cspFormSummaries = {
        attach: function (context) {
            $(context).find('.wmcontent-security-policy-form .vertical-tabs details').drupalSetSummary(function (element) {
                return element.dataset.description
                    .replace(/&/g, '&amp;')
                    .replace(/>/g, '&gt;')
                    .replace(/</g, '&lt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&apos;');
            });
        }
    };
})(jQuery);
