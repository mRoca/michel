$(function () {
    var load = false;
    var offset = $('.box:last').offset();
    var loader = $('div#loadmoreajaxloader');

    $(window).scroll(function () {
        if ((offset.top - $(window).height() <= $(window).scrollTop()) && load === false) {
            load = true;
            loader.show();

            var page = parseInt(loader.attr('data-page')) + 1;

            $.ajax({
                type: "GET",
                url: loader.attr('data-url'),
                data: {'page': page},
                success: function (msg) {
                    if (msg.length > 0) {
                        $('div#loadmoreajaxloader').hide();
                        $('div#job-container').append(msg);
                        $('#loadmoreajaxloader').attr('data-page', page.toString());
                        offset = $('.box:last').offset();
                        load = false;
                    } else {
                        $('div#loadmoreajaxloader').html(loader.attr('data-empty'));
                    }
                }
            });
        }
    });

    $(document).on('click', '#job-container .box', function (e) {
        if (!$(e.target).is('a')) {
            document.location = $(this).find('a.title').attr('href');
        }
    });

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

});
