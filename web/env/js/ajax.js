
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
         refreshGameView();
    

})(jQuery);


