$(function() {

    var ajaxCounter = 1; // variable um zu pruefen in welche Reihenfolge AJAX-Requests verschickt wurden

    var removeFromArrayByValue = function(arr, item) {
        var index = arr.indexOf(item);
        if (index !== -1) arr.splice(index, 1);
    };

    var scrollTo = function(element, speed) {
        if(element === 0) {
            $('html, body').animate({
                scrollTop: 0
            }, speed);
        }
        else {
            var heightHeader = $(".top").outerHeight();
            var offsetElement = $(element).offset().top;

            $('html, body').animate({
                scrollTop: offsetElement - heightHeader - 40
            }, speed);
        }
    };

    /** returnt eine Funktion, die "callback" nach "delay" milisekunden aufruft, wenn sie ausgeführt wird.
     *  Wenn während des delays die returnte Funktion noch ein mal aufgerufen wird, wird der delay resetet.
     */
    function debounce(callback, delay) {
        var timeout;
        return function() {
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                callback.apply(this, args)
            }.bind(this), delay)
        }
    }


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

    // Bei Klick auf link mit Hash(#), Burger-Menu schliessen:
    $(".close-burger-trigger").on("click", function() {
        $burgerClose.trigger("click");
    });


    // Suchfilter Menu:
    var $sucheButton = $(".suchfilter-btn");
    var $sucheMenu = $("#suche-overlay");
    var $inhalt = $(".inhalt");
    var $allGridItems = $(".grid-item");

    var resizeSucheMenu = function() {
        var hoehe = $(".top").outerHeight();
        $sucheMenu.css("top", hoehe + "px");
    };
    resizeSucheMenu();

    $sucheButton.on("click", function() {
        var isClosed = true;

        if($sucheMenu.hasClass("open"))
            isClosed = false;

        if(isClosed) {
            resizeSucheMenu();
            $sucheMenu.fadeIn(350);
            $inhalt.animate({opacity: 0}, 350, 'linear', function() {
                $allGridItems.addClass("mini");
                $masoryGrid.masonry('layout');
            });
            $sucheMenu.addClass("open");
            setTimeout(function() {
                scrollTo(0, 0);
            }, 300);
        }
        else{
            $allGridItems.removeClass("mini");
            $masoryGrid.masonry('layout');
            setTimeout(function() {
                $allGridItems.removeClass("mini");
                $masoryGrid.masonry('layout');
            }, 300);
            $sucheMenu.fadeOut(350);
            $inhalt.animate({opacity: 100}, 350);
            $sucheMenu.removeClass("open");
        }
    });



    // Klick auf ausgeblendete Archivierungen unterbinden:
    $(document).on("click", ".mini, .mini a", function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });



    // Image Zoom (Desktop):
    $('.detail-zoom').each(function() {
        var $this = $(this);
        var bigImage = $this.data("zoom-image");
        $this.zoom({url: bigImage, magnify:0.5});
    });


    // Image Zoom (mobil): TODO



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


    // Resize-Buttons durch Cookies initialisieren:
    var gridCookie = Cookies.get(gridCookieName);
    if(gridCookie) {
        $(".resize-buttons button.resize-btn-" + gridCookie).trigger("click");
    }
    else
        $(".resize-btn-mittel").addClass("active-btn").removeClass("inactive-btn");


    // :::Favoriten:::

    // Favoriten initialisieren:
    var favCookieName = "favoriten";
    var favCookieString = "";
    var favCookieArray = null;

    var updateFavArray = function() {
        favCookieString = Cookies.get(favCookieName);

        if(favCookieString !== undefined && favCookieString !== "") {
            favCookieArray = favCookieString.split("-");
        }
        else{
            favCookieArray = [];
        }
    };

    updateFavArray();


    // Favoriten-Seite öffnen Button:
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


    // Favoriten hinzufügen/enfernen Buttons:
    var $addFavButtons = $(".add-fav-button");

    var updateAddFavButtons = function() {
        $addFavButtons.each(function() {
            var $this = $(this);

            var $detailFavButtonVoll = $this.find(".add-fav-button-img-voll");
            var $detailFavButtonLeer = $this.find(".add-fav-button-img-leer");
            var detailId = $this.data("id");

            if( favCookieArray.indexOf(""+ detailId) > -1 ) {
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

        updateFavArray();

        if( favCookieArray.indexOf("" + detailId) > -1 ) {
            removeFromArrayByValue(favCookieArray, "" + detailId);
        }
        else{
            favCookieArray.push("" + detailId);
        }

        updateAddFavButtons();
        updateHeaderFavButton();

        Cookies.set(favCookieName, favCookieArray.join("-"), cookieExpire50Years);
    });

    // Alle Favoriten löschen Button:
    var $deleteAllFavsSection = $(".favorites-delete-all");
    var $deleteAllFavsButton = $deleteAllFavsSection.find(".favorites-delete-all-button");
    var $deleteAllFavsConfirm = $deleteAllFavsSection.find(".favorites-delete-all-confirm");
    var $deleteAllFavsBack = $deleteAllFavsSection.find(".favorites-delete-all-back");
    var $deleteAllFavsQuestion = $deleteAllFavsSection.find(".favorites-delete-all-question");

    $deleteAllFavsButton.on("click", function() {
        $deleteAllFavsButton.fadeOut(0);
        $deleteAllFavsConfirm.fadeIn(0);
        $deleteAllFavsBack.fadeIn(0);
        $deleteAllFavsQuestion.fadeIn(0);
    });
    $deleteAllFavsBack.on("click", function() {
        $deleteAllFavsButton.fadeIn(0);
        $deleteAllFavsConfirm.fadeOut(0);
        $deleteAllFavsBack.fadeOut(0);
        $deleteAllFavsQuestion.fadeOut(0);
    });
    $deleteAllFavsConfirm.on("click", function() {
        Cookies.set(favCookieName, "", cookieExpire50Years);

        window.location.href = $(this).data("redirect");
    });




    // ionRangeSlider:
    $(".js-range-slider").ionRangeSlider({
        skin: "square"
    });
    var ionRangeSlider = $(".js-range-slider").data("ionRangeSlider");



    // Bei Window-Resize die Menüs neu ausrichten und lazy updaten:
    $(window).on("resize", function() {
        resizeBurgerMenu();
        resizeSucheMenu();

        setTimeout(function() {
            $lazyInstance.update();     // Bilder nachladen, die nach Resize in Sichtfeld sind
        }, 200);
    });



    // Anchor-Scrolling animieren beim drücken eines Scroll-Buttons:
    $(".scroll-button").on("click", function() {
        var $this = $(this);
        var href = $this.attr("href");
        var ziel;

        if(href === "#")
            ziel = 0;
        else
            ziel = $(href);

        scrollTo(ziel, "slow");
    });

    // Anchor-Scrolling animieren beim Seitenaufbau:
    var windowHref = window.location.href;
    var windowHash = windowHref.split("#")[1];

    if(windowHash) {
        var ziel = $("#" + windowHash);
        if(ziel.length) {
            scrollTo(0, 0);
            scrollTo(ziel, "slow");
        }
    }




    // :::SUCHE:::

    var active = "active";
    var inactive = "inactive";
    var $inputFrames = $sucheMenu.find(".input-frame");
    var $infoFrames = $inputFrames.filter(".info-frame");
    var $blaupausen = $("#blaupausen");
    var $auswahlBlaupause = $blaupausen.find(".auswahl-item").first();
    var $vorschlagBlaupause = $blaupausen.find(".vorschlag-item").first();
    var $allInputs = $inputFrames.find("input");
    var $addInputs = $allInputs.filter(".add-input");


    // // Erst beim ersten Öffnen der Suche Jahrfilter inactive geben, damit ion-Range sich richtig initialisieren kann
    // var firstSucheButtonClick = true;
    // $sucheButton.on("click", function() {
    //     if(firstSucheButtonClick) {
    //         setTimeout(function() {
    //             $inputFrames.filter(".jahr-frame").addClass(inactive);
    //         } ,230);
    //         firstSucheButtonClick = false;
    //     }
    // });


    // öffnen des Frames bei Klick auf Label
    $inputFrames.find(".outer-label").on("click", function() {
        $inputFrames.removeClass(active).addClass(inactive);
        $(this).parent().addClass(active).removeClass(inactive);
    });


    // Fügt der Auswahlliste einen Eintrag hinzu
    var addToAuswahl = function($input, $auswahlContainer, $vorschlaegeContainer, wert) {

        var stringAlreadyExistst = false;
        $input.focus();

        $auswahlContainer.find("li span").each(function() {

            if( $(this).text() == wert ) {
                stringAlreadyExistst = true;
            }
        });

        if( !stringAlreadyExistst ) {

            $vorschlaegeContainer.children().remove();

            var $newItem = $auswahlBlaupause.clone();
            $newItem.data("wert", wert);
            $newItem.find("span").text(wert);

            $newItem.find("button").on("click", function() {
                var li = $(this).parent();
                deleteFromAuswahl(li);
            });

            $newItem.appendTo($auswahlContainer);

            $input.val(null); // input leerern
            sendAjaxRequest($input); // Neue Vorschlaege requesten
        }
    };

    var deleteFromAuswahl = function(auswahlItem) {
        $(auswahlItem).remove();
    };


    // Funktion die aufgerufen wird, wenn ein Vorschlag-Span gedrückt wird
    var fromVorschlaegeToAuswahl = function () {
        var $this = $(this);
        var $thisItem = $this.parent();
        var thisWert = $thisItem.data("wert");

        console.log(thisWert);

        var $thisVorschlaegeContainer = $thisItem.parent();

        var $thisInputFrame = $thisVorschlaegeContainer.parent().parent();
        var $thisAddInput = $thisInputFrame.find(".add-input");
        var $thisAuswahlContainer = $thisInputFrame.find(".auswahl ul");

        addToAuswahl($thisAddInput, $thisAuswahlContainer, $thisVorschlaegeContainer, thisWert);
    };


    // Bei SUBMIT vor dem Abschicken die Auswahlen in die hidden-inputs schreiben:
    $("#suche-form").on("submit", function() {

        $infoFrames.each(function() {
            var $this = $(this);
            var realWert = "";

            $this.find(".auswahl .auswahl-item").each(function() {
                var wert = $(this).data("wert");

                if(wert) {
                    realWert += ";"+wert;
                }
            });

            $this.find(".real-input").val(realWert);
        });
    });



    // Vorschäge per AJAX updaten:

    var route = $("#ajax-route").data("route");


    // Updatet die Such-Vorschlaege:
    var updateVorschlaege = function(vorschlaegeArray, $inputFrame, thisAjaxCounter) {

        // Nur wenn Die Ajax-Antwort zum aktuellsten Ajax-Request passt, Vorschlaege updaten
        if(thisAjaxCounter == ajaxCounter) {

            var $vorschlaegeContainer = $inputFrame.find(".vorschlaege ul");
            $vorschlaegeContainer.children().remove();

            for(var i = 0; i < vorschlaegeArray.length; i++) {
                var wert = vorschlaegeArray[i].wert;

                var $newItem = $vorschlagBlaupause.clone();
                $newItem.data("wert", wert);

                var $newItemSpan = $newItem.find("span");
                $newItemSpan.text(wert);

                $newItemSpan.on("click", fromVorschlaegeToAuswahl);

                $newItem.appendTo($vorschlaegeContainer);
            }
        }
    };


    // Sendet Ajax-Request an Server und wartet auf Antwort:
    var sendAjaxRequest = function($addInput) {
        var thisAjaxCounter = ++ajaxCounter;

        var wert = $addInput.val();
        var $inputFrame = $addInput.parent();
        var name = $inputFrame.find(".real-input").prop("name");
        var infoPicked = "";
        $inputFrame.find(".auswahl-item").each(function() {
            infoPicked += ";"+ ( $(this).text()+"" ).trim();
        });

        $.ajax({
            url: route,
            type: "POST",
            dataType: "json",
            data: {
                "infoName": name,
                "infoWert": wert,
                "infoPicked" : infoPicked
            },
            async: true,
            success: function(data) {
                updateVorschlaege(data.vorschlaege, $inputFrame, thisAjaxCounter);
            }
        });
    };


    // Funktion zum Starten/Neustarten des Countdowns:
    var ajaxRequestTrigger = debounce(
        function($addInput) {
            sendAjaxRequest($addInput);
        },
        700
    );


    // --- Wenn Eingabe erfolgt, countdown starten/neustarten:
    $addInputs.on('input paste', function() {
        var $this = $(this);
        ajaxRequestTrigger($this);
    });




    // Bei ENTER neues Auswahl-Item erstellen:
    $addInputs.on("keypress", function(e) {
        if (e.which == 13) {
            e.preventDefault();

            var $this = $(this);
            var wert = $this.val();

            if(wert) {
                var $inputFrame = $this.parent();
                var $auswahlContainer = $inputFrame.find(".auswahl ul");
                var $vorschlaegeContainer = $inputFrame.find(".vorschlaege ul");

                addToAuswahl($this, $auswahlContainer, $vorschlaegeContainer, wert); // Eingabe zu Auswahl hinzufuegen
            }
        }
    });


    // Auch auf Vorschlaege die Beim Laden der Seite vorhanden sind Click-Event binden:
    $infoFrames.find(".vorschlag-item span").on("click", fromVorschlaegeToAuswahl);


    // Auch auf AuswahlItems die Beim Laden der Seite vorhanden sind Click-Event binden:
    $infoFrames.find(".auswahl-item button").on("click", function() {
        var li = $(this).parent();
        deleteFromAuswahl(li);
    });


    // Bei RESET Auswahl killen, ionRange reseten und neue Vorschlaege per AJAX Rrequesten:
    $("#suche-form").on("reset", function() {

        ionRangeSlider.reset();

        $infoFrames.each(function() {
            var $this = $(this);

            $this.find(".auswahl .auswahl-item").remove();

            var $addInput = $this.find(".add-input");
            sendAjaxRequest($addInput);
        });
    });





    




});
