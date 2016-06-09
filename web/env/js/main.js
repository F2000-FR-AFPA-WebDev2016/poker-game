
(function ($) {

    $(function () {
        var $window = $(window),
                $body = $('body'),
                $first = $('#firstImg'),
                $second = $('#secondImg'),
                $audio = $('#audio audio'),
                $val = $audio.attr('src'),
                $tabAllu = [1000,
                    20, 20, 20, 20, 20, 20, 20,
                    600, 200, 200,
                    20, 20, 20, 20, 20, 20, 20, 20, 20, 20,
                    700, 100],
                $current = 0;

        $window.on('load', function () {
            window.setTimeout(function () {
                $body.addClass('is-loading');
                $first.removeClass('loading');
                loadImage();
                loadAudio();
            }, 800);
        });

        function loadImage() {
            $.each($tabAllu, function () {
                $current = $current + this;
                window.setTimeout(function () {
                    $second.toggleClass('loading');
                }, $current);
            });
        }

        function loadAudio() {
            window.setTimeout(function () {
                $audio.attr('src', $val + 'click.mp3');
            }, 500);
            window.setTimeout(function () {
                $audio.attr('src', $val + 'elec.mp3');
            }, 1000);
            window.setTimeout(function () {
                $audio.attr('src', $val + '');
            }, 3200);
        }

        $(document).on('click', '#openFooter', function () {
            $('#credits').toggle('slow', function () {})
        })
    });
})(jQuery);


var tootye = (function () {
    "use strict";
    var app = {
        tag: {
            head: {},
            body: {}
        },
        id: {},
        valScroll: {
            top: $(window).scrollTop(),
            bottom: $(window).scrollTop() + $(window).height(),
        },
        init: function () {

            $.each($('*'), function ( ) {
                app.id[$(this).attr('id')] = {
                    "name": "#" + $(this).attr('id'),
                    "loc": $("#" + $(this).attr('id')),
                    "width": parseInt($(this).outerWidth(true)),
                    "height": parseInt($(this).outerHeight(true)),
                    "positionHaut": $(this).offset().top,
                    "positionBas": $(this).offset().top + parseInt($(this).outerHeight(true)),
                    "tier": parseInt($(this).outerHeight(true)) / 3 + $(this).offset().top,
                    "tier2": parseInt($(this).outerHeight(true)) / 3 * 2 + $(this).offset().top
                },
                app.body($(this))

            }),
                    app.scroll()

        },
        body: function (val) {
            val = val.context.tagName.toLowerCase();
            if (!app.tag.body[val]) {
                app.tag.body[val] = $(val)
            }
        },
        actuScroll: function () {
            app.valScroll.top = $(window).scrollTop(),
                    app.valScroll.bottom = $(window).scrollTop() + $(window).height()
        },
        scroll: function () {
            app.actuScroll();
            $.each(app.tag.body.section, function ( ) {
                var tab = app.id[($(this).attr('id'))],
                        nom = tab.name,
                        dep = tab.positionHaut,
                        arr = tab.positionBas,
                        tier = tab.tier,
                        tier2 = tab.tier2,
                        haut = tab.positionHaut,
                        bas = tab.positionBas;
                if (tier < app.valScroll.bottom && tier2 > app.valScroll.top) {
                    $(this).addClass('show');
                    $(this).removeClass('hide');
                } else {
                    $(this).removeClass('show');
                    $(this).addClass('hide');
                }

            });
        },
        head: function (meta) {
            $.each($(meta).children(), function ( ) {

                app.chaine = app.chaine + ', ' + this.tagName;
            });

        },
    };
    app.init();
    return app;
})();
