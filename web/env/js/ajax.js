(function ($) {
    var $arrayPath = window.location.pathname.split('/'),
            $dev = false,
            $cible = '';



    if ($arrayPath.length > 2) {
        if ($arrayPath[1] === 'app_dev.php') {
            $dev = true;
            $cible = $arrayPath[2] === '' ? 'home' : $arrayPath[2];
        } else {
            $cible = $arrayPath[1] === '' ? 'home' : $arrayPath[1];
        }
    } else {
        $cible = $arrayPath[1] === '' ? 'home' : $arrayPath[1];
    }



    /*function refreshGameView() {
     // appel JQuery
     // + modification $('#game')

     // TODO : timer

     var game = $('#game'),
     table = $('.tableNumber').html();
     $.ajax({
     url: "../view/"+table,
     method: 'POST',
     success: function (data) {
     game.html(data);
     }

     });

     window.setTimeout(function () {
     refreshGameView();
     }, 3000);

     }
     refreshGameView();*/

    function refreshListTable() {
        // appel JQuery
        // + modification $('#game')

        // TODO : timer

        var $table = $('#list_table table');
        $.ajax({
            url: "listTableRefresh",
            method: 'POST',
            success: function (data) {
                $table.html(data);
            }

        });

        /*window.setTimeout(function () {
         refreshListTable();
         }, 3000);*/
    }

    function openTable() {
        var $table = $('#openRefresh'),
                $open = $('footer span.table');

        $.each($open, function () {
            var $numTable = $(this).attr('class').split(' '),
                    $permission = $(this).children('span.permission').html(),
                    $ouverture = $(this).children('span.ouverture').html();
            if ($ouverture === '' && $permission === '1') {
                var myWindow = window.open("http://poker-game.dev/app_dev.php/play/" + $numTable[1], "_blank");
            }
        });


        $.ajax({
            url: "openTableRefresh",
            method: 'POST',
            success: function (data) {
                $table.html(data);
            }

        });
    }
    if ($cible === 'listTable') {
        refreshListTable();
    }

    if ($cible !== 'play') {
        window.setInterval(function () {
            openTable();
        }, 5000);
    }
    console.log($cible);





    $('#betting').on('click', function (e) {
        e.preventDefault();
        var table = $('.tableNumber').html(),
                action = e.target.id.split('_')[1];


        $.ajax({
            url: "../" + action + "/" + table,
            method: 'POST',
            success: function (data) {
                $('#betting').html(data);
            },
        })
    })

})(jQuery);

