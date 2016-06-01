
(function ($) {
    var $arrayPath = window.location.pathname.split('/'),
        $dev = false,
        $cible = '';
        
        if($arrayPath.length > 2){
            if($arrayPath[1] === 'app_dev.php'){
                $dev = true;
                $cible = $arrayPath[2] === '' ? 'home' : $arrayPath[2];
            }else{
                $cible = $arrayPath[1] === '' ? 'home' : $arrayPath[1];
            }
        }else{
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
             
            window.setTimeout(function () {
                     refreshListTable();
            }, 3000);

    }
    if($cible === 'listTable'){
        refreshListTable();
    }
    
    

})(jQuery);


