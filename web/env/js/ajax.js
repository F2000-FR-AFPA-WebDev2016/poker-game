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

    function initialisePlay(){
        var $table = $('section .tableNumber').html(),
            $game = $('#game');
        $.ajax({
            url: "../newMain/"+$table,
            method: 'POST',
            success: function (data) {
                $game.html(data);
            }

        });
    }
    
    if($('#game .initialise') !== null){
        
        window.setTimeout(function () {
            initialisePlay();
        }, 5000);
        window.setTimeout(function () {
            $('#game .banque .pot').html('Tirage du dealer')
        }, 2000);
    }

    function refreshListTable() {

        var $table = $('#list_table table');
        $.ajax({
            url: "listTableRefresh",
            method: 'POST',
            success: function (data) {
                $table.html(data);
            }

        });
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
        window.setInterval(function () {
            refreshListTable();
        }, 3000);
    }

    if ($cible !== 'play') {
        window.setInterval(function () {
            openTable();
        }, 5000);
    }

    
/*
    $('#betting form').on('click', function (e) {
    
        e.preventDefault();
        var table = $('.tableNumber').html(),
                action = e.target.id.split('_')[1],
                token = $('#form__token').attr('value');


        $.ajax({
            url: "../view/" + table,
            method: 'POST',
            data : { 'form[bet]': 400, 'form[check]': "", 'form[_token]' : token},
            success: function (data) {
                $('#betting').html(data);
            },
        })
    })*/
    
    

})(jQuery);

