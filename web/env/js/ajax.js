(function ($) {
    var $arrayPath = window.location.pathname.split('/'),
            $dev = false,
            $nbOpen = 0,
            $cible = '',
            $play = 0;
    

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

    function initialiseVersDeal(){
        var $table = $('section .tableNumber').html(),
            $game = $('#game');
        $.ajax({
            url: "../deal/"+$table,
            method: 'POST',
            success: function (data) {
                $game.html(data);
            }

        });
        window.setTimeout(function () {
            $('#game .banque .pot').html('Tirage du dealer')
            distribCartes();
        }, 6000);
        
    }
    
    function distribCartes(){
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
    
    if($('#game .initialise').length > 0){
        
        var $departPartie = parseFloat($('#game .initialise span').attr('class') + '000') ,
            $attente = 10000,
            $refresh = $.now() ,
            difference = ($departPartie - $refresh + $attente);
    
        window.setTimeout(function () {
            initialiseVersDeal();
        }, difference);
    }
    
    if($('#game .dealCards').length > 0){
        initialiseVersDeal();
    }
    
    $( "#game .gameEnCours" ).load(function() {
        console.log('oui');
    });
    
    $(document).ready(function() {
        loadGame();
    });
    
    function loadGame(){
        var $table = $('section .tableNumber').html(),
            $game = $('#game');
        if($( "#game .gameEnCours" ).length > 0 && $( "#playSuiv" ).length === 0){
            $.ajax({
                url: "../newMain/"+$table,
                method: 'POST',
                success: function (data) {
                    $game.html(data);
                }
            });
        }
        window.setTimeout(function () {
            loadGame();
        }, 2000);
    }
    
    $(document).on('click', '#playSuiv span', function (e) {
        var action = e.target.className;
        if(action === 'bet'){
            if($('#playSuiv .inputBet input').length > 0 && $('#playSuiv .inputBet input').val() > 0){
                var raise = parseInt($('#playSuiv .inputBet input').val());
                actionPlayGame(action, "/"+raise);
            }else if(parseInt($('#playSuiv .bet p').html()) > 0 ){
                var raise = parseInt($('#playSuiv .bet p').html());
                actionPlayGame(action, "/"+raise);
            }
            
        }else if(action === 'raise'){
            var raise = $('#playSuiv .raise p').html() === '' ? 0 : parseInt($('#playSuiv .raise p').html()),
                input = parseInt($('#playSuiv .inputRaise input').val()),
                diff  = input - raise;
            if($('#playSuiv .inputRaise input').val() > 0){
                actionPlayGame(action, "/"+diff);
            }
        }else if(action === 'fold' | action === 'check'){
            actionPlayGame(action, "");
        }
    });

    function actionPlayGame(action, val){
        var $table = $('section .tableNumber').html(),
            $game = $('#game');
        $.ajax({
                url: "../" + action + "/" + $table + val,
                method: 'POST',
                success: function (data) {
                    $game.html(data);
                },
            })
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
                $ouverture = $(this).children('span.ouverture').html('1');
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
        
        if ($cible !== 'play' && $('footer span.table').length > 0) {
            if($nbOpen < 3){
                window.setTimeout(function () {
                    openTable();
                    $nbOpen++;
                }, 500);
            }else{
                window.setTimeout(function () {
                    openTable();
                }, 3000);
            }
        }
    }
    if ($cible === 'listTable') {
        window.setInterval(function () {
            refreshListTable();
        }, 3000);
    }

    if ($cible !== 'play' && $('footer span.table').length > 0) {
        
        window.setTimeout(function () {
            openTable();
        }, 100);
        
    }

    if ($cible === 'play' && $play < 5) {
        setInterval(
        function ()
        {
          $('#load_donnees').load('mes_donnees.php').fadeIn("slow");
        }, 10000);
        
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

