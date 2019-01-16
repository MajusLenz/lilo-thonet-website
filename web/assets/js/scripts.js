$(function() {

    // Masory Grid:
    var $masoryGrid = $('.grid').masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        percentPosition: true
    });


    // Lazy Loading:
    $lazyInstance = $('.lazy').Lazy({
        chainable: false,
        scrollDirection: 'vertical',
        effect: 'fadeIn',
        effectTime: 350,
        threshold: 500,
        onError: function(element) {
            console.log('error loading ' + element.data('src'));
        },
        afterLoad: function(element) {
            //$(element).css("top", "0px"); // Animation

            $masoryGrid.masonry('layout');    // Masory Grid wieder richtig ausrichten
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
    var $burgerMenu = $("#burger-overlay");

    $burgerOpen.on("click", function() {
        $burgerOpen.fadeOut(0);
        $burgerClose.fadeIn(0);

        $burgerMenu.fadeIn(320);
    });
    $burgerClose.on("click", function() {
        $burgerClose.fadeOut(0);
        $burgerOpen.fadeIn(0);

        $burgerMenu.fadeOut(400);
    });


    // Image Zoom (Desktop):
    $('.detail-zoom').each(function() {
        var $this = $(this);
        var bigImage = $this.data("zoom-image");
        $this.zoom({url: bigImage, magnify:0.5});
    });


    // Resize-Buttons:
    $(".resize-buttons button").on("click", function(e) {
        var $this = $(this);
        var active = "active-btn";
        var inactive = "inactive-btn";

        if($this.hasClass(active))
            return;

        $(".resize-buttons .active-btn").removeClass(active).addClass(inactive);
        $this.removeClass(inactive).addClass(active);

        var grid = "grid";
        var gridGross = "grid-gross";
        var gridMittel = "grid-mittel";
        var gridKlein = "grid-klein";

        var gridSizer = "grid-sizer";
        var gridItem = "grid-item";
        var gridItemGross = "grid-item-gross";
        var gridItemMittel = "grid-item-mittel";
        var gridItemKlein = "grid-item-klein";

        var groesse = $this.data("size");

        var grids = $("."+grid);
        var gridItems = $("."+gridItem +", ."+ gridSizer);

        grids.removeClass(gridKlein).removeClass(gridMittel).removeClass(gridGross);
        gridItems.removeClass(gridItemKlein).removeClass(gridItemMittel).removeClass(gridItemGross);

        switch(groesse) {
            case "gross":
                grids.addClass(gridGross);
                gridItems.addClass(gridItemGross);
                break;
            case "mittel":
                grids.addClass(gridMittel);
                gridItems.addClass(gridItemMittel);
                break;
            case "klein":
                grids.addClass(gridKlein);
                gridItems.addClass(gridItemKlein);
                break;
        }

        $masoryGrid.masonry('layout');    // Masory Grid wieder richtig ausrichten

        setTimeout(function() {
            $lazyInstance.update();     // Bilder nachladen, die nach Resize in Sichtfeld sind
        }, 200);
    });



});
