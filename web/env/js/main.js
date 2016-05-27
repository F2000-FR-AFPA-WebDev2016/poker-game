
(function($) {

    $(function() {
        
        var	$window = $(window),
                $body = $('body'),
                $first = $('#firstImg'),
                $second = $('#secondImg'),
                $tabAllu = [1000, 
                            20,20,20,20,20,20,20, 
                            600, 200, 200,
                            20,20,20,20,20,20,20,20,20,20,
                            700, 100],
                $current = 0;
        
        $window.on('load', function() {
            window.setTimeout(function() {
                    $body.addClass('is-loading');
                    $first.removeClass('loading');
                    loadImage();
            }, 800);
        });
        
        function loadImage(){
            $.each($tabAllu, function(){
                $current = $current + this;
                window.setTimeout(function() {
                    $second.toggleClass('loading');
                }, $current);
            });
        }
        
        

    });

})(jQuery);


