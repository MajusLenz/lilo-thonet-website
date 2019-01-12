$(function() {

    // Mansory Grid:
    var $grid = $('.grid').masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        percentPosition: true
    });


    // Lazy Loading:
    $('.lazy').Lazy({
        scrollDirection: 'vertical',
        effect: 'fadeIn',
        effectTime: 350,
        threshold: 500,
        onError: function(element) {
            console.log('error loading ' + element.data('src'));
        },
        afterLoad: function(element) {
            //$(element).css("top", "0px");  // Animation

            $grid.masonry('layout');    // Mansory Grid wieder richtig ausrichten
        }
    });


    //Rechtsklick verbieten:
    $(".rightclick-alert").on("contextmenu", function(e) {
        alert("Diese Grafik ist kopiergeschützt! Wenn Sie etwas herunterladen möchten, kontaktieren Sie bitte den Administrator.");
        e.stopPropagation();
        return false;
    });


    // Hamburger Menu:
    var $burgerOpen = $(".burger-button-open");
    var $burgerClose = $(".burger-button-close");

    $burgerOpen.on("click", function() {
        $burgerOpen.fadeOut(0);
        $burgerClose.fadeIn(0);

        // Menu anzeigen TODO
    });
    $burgerClose.on("click", function() {
        $burgerClose.fadeOut(0);
        $burgerOpen.fadeIn(0);

        // Menu ausblenden TODO
    });


    // Image Zoom:
    $('.detail-zoom').each(function() {
        var $this = $(this);
        var bigImage = $this.data("zoom-image");
        $this.zoom({url: bigImage, magnify:0.5});
    });

});
