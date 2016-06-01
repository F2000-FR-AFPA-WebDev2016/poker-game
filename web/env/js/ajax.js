
(function ($) {


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
    refreshListTable();
    

})(jQuery);


