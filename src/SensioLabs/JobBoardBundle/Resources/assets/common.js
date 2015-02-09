$(function () {

    var lang = $('html').attr('lang') || 'en';

    if ($.datepicker) {
        $.datepicker.setDefaults($.datepicker.regional[lang]);
        $(".datepicker").datepicker({'dateFormat': 'mm/dd/yy'});
    }

    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.config.language = lang;
    }

    function autocompleteField($target, dataCallback) {
        var source = $target.attr('data-source');
        if (!source || !dataCallback) return;

        $target.autocomplete({
            source: function (req, reponse) {
                $.ajax({
                    url: source,
                    type: 'get',
                    dataType: 'json',
                    data: dataCallback(req),
                    cache: true,
                    success: function (data) {
                        reponse(data);
                    }
                });
            },
            minLength: 2
        });
    }

    $('input.company-city').each(function () {
        var self = $(this);
        autocompleteField(self, function (req) {
            return {
                'search': req.term,
                'country': self.closest('form').find('.company-country').val()
            };
        });
    });

    $('input.company-name').each(function () {
        var self = $(this);
        autocompleteField(self, function (req) {
            return {
                'search': req.term,
                'country': self.closest('form').find('.company-country').val(),
                'city': self.closest('form').find('.company-city').val()
            };
        });
    });
});
