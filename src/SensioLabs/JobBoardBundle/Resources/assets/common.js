$(function () {

    var lang = $('html').attr('lang') || 'en';

    $('.filter-action').on('click', function (e) {

        e.preventDefault();

        var $this = $(this);
        var currentAction = $this.attr('data-filter-action');
        var nextAction = currentAction == 'up' ? 'down' : 'up';
        var $container = $this.closest('ul');

        $container.find('li:gt(3):not(:last)').each(function () {
            nextAction == 'down' ? $(this).hide(300) : $(this).show(300);
        });

        $container.find('[data-filter-action=' + currentAction + ']').hide();
        $container.find('[data-filter-action=' + nextAction + ']').show();
    });

    $('.filter li.active:not(:visible)').parent().find('a[data-filter-action=down]').click();


    if ($.datepicker) {
        $.datepicker.setDefaults($.datepicker.regional[lang]);
        $(".datepicker").datepicker({'dateFormat': 'mm/dd/yy'});
    }

    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.config.language = lang;
    }
});
