
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
    });


    function refreshGameView() {
        // appel JQuery
        // + modification $('#game')

        // TODO : timer
        $.ajax({
            url: '../view/8',
            type: 'POST',
            success: function (data) {
                $('#game').html(data);
            },
            error: function (data) {
                console.log(data);
            },
        })
        window.setTimeout(function () {
            refreshGameView();
        }, 3000);
    }
    ;
    refreshGameView();

})(jQuery);


