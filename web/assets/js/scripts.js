$(function() {

    var removeFromArrayByValue = function(arr, item) {
        var index = arr.indexOf(item);
        if (index !== -1) arr.splice(index, 1);
    };


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
            console.log('error loading: ' + element.data('src'));
        },
        afterLoad: function(element) {
            //$(element).css("top", "0px"); // Animation

            $masoryGrid.masonry('layout');    // Masory Grid wieder richtig ausrichten
        },
        onFinishedAll: function() {
            //$(".footer").fadeIn();    // Footer erst einblenden wenn alle Bilder geladen wurden
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

    var resizeBurgerMenu = function() {
        var hoehe = $(".header-balken1, .header-balken1 + hr").outerHeight() +0.8;
        $burgerMenu.css("top", hoehe + "px");
    };
    resizeBurgerMenu();

    $burgerOpen.on("click", function() {
        $burgerOpen.fadeOut(0);
        $burgerClose.fadeIn(0);

        resizeBurgerMenu();
        $burgerMenu.fadeIn(320);
    });

    $burgerClose.on("click", function() {
        $burgerClose.fadeOut(0);
        $burgerOpen.fadeIn(0);

        $burgerMenu.fadeOut(400);
    });



    // Suchfilter Menu:
    var $sucheButton = $(".suchfilter-btn");
    var $sucheMenu = $("#suche-overlay");

    var resizeSucheMenu = function() {
        var hoehe = $(".top").outerHeight();
        $sucheMenu.css("top", hoehe + "px");
    };
    resizeSucheMenu();

    $sucheButton.on("click", function() {
        resizeSucheMenu();
        $sucheMenu.fadeToggle(350);

        $("#testInput").focus();    // TODO
    });



    // Image Zoom (Desktop TODO):
    $('.detail-zoom').each(function() {
        var $this = $(this);
        var bigImage = $this.data("zoom-image");
        $this.zoom({url: bigImage, magnify:0.5});
    });



    // Resize-Buttons:
    var gridCookieName = "grid-size";
    var cookieExpire50Years = {expires: 18250};

    $(".resize-buttons button").on("click", function() {
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
                Cookies.set(gridCookieName, "gross", cookieExpire50Years);
                break;
            case "mittel":
                grids.addClass(gridMittel);
                gridItems.addClass(gridItemMittel);
                Cookies.set(gridCookieName, "mittel", cookieExpire50Years);
                break;
            case "klein":
                grids.addClass(gridKlein);
                gridItems.addClass(gridItemKlein);
                Cookies.set(gridCookieName, "klein", cookieExpire50Years);
                break;
        }

        $masoryGrid.masonry('layout');    // Masory Grid wieder richtig ausrichten

        setTimeout(function() {
            $lazyInstance.update();     // Bilder nachladen, die nach Resize in Sichtfeld sind
        }, 200);
    });


    // Resize-Buttons mit Cookies initialisieren:
    var gridCookie = Cookies.get(gridCookieName);
    if(gridCookie) {
        $(".resize-buttons button.resize-btn-" + gridCookie).trigger("click");
    }


    // Favoriten Buttons:
    var favCookieName = "favoriten";
    var favCookieString = Cookies.get(favCookieName);
    var favCookieArray = [];

    if(favCookieString !== undefined && favCookieString !== "") {
        favCookieArray = favCookieString.split("-");
    }


    var $headerFavButton = $(".alle-fav-button");
    var $headerFavButtonVoll = $headerFavButton.find(".alle-fav-button-img-voll");
    var $headerFavButtonLeer = $headerFavButton.find(".alle-fav-button-img-leer");

    var updateHeaderFavButton = function() {
        if(favCookieArray.length > 0) {
            $headerFavButtonVoll.fadeIn(0);
            $headerFavButtonLeer.fadeOut(0);
        }
        else{
            $headerFavButtonVoll.fadeOut(0);
            $headerFavButtonLeer.fadeIn(0);
        }
    };
    updateHeaderFavButton();


    var $addFavButtons = $(".add-fav-button");

    var updateAddFavButtons = function() {

        $addFavButtons.each(function() {
            var $this = $(this);
            var $detailFavButtonVoll = $this.find(".add-fav-button-img-voll");
            var $detailFavButtonLeer = $this.find(".add-fav-button-img-leer");
            var detailId = $this.data("id");

            if( favCookieArray.includes(""+ detailId) ) {
                $detailFavButtonVoll.fadeIn(0);
                $detailFavButtonLeer.fadeOut(0);
            }
            else{
                $detailFavButtonVoll.fadeOut(0);
                $detailFavButtonLeer.fadeIn(0);
            }
        });
    };
    updateAddFavButtons();


    $addFavButtons.on("click", function() {

        var $this = $(this);
        var detailId = $this.data("id");

        if( favCookieArray.includes("" + detailId) ) {
            removeFromArrayByValue(favCookieArray, "" + detailId);
        }
        else{
            favCookieArray.push("" + detailId);
        }

        updateAddFavButtons();
        updateHeaderFavButton();

        Cookies.set(favCookieName, favCookieArray.join("-"), cookieExpire50Years);
    });



    // Bei Window-Resize die Menüs neu ausrichten und lazy + mansory updaten:
    //TODO



});
