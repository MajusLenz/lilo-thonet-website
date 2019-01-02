$(function() {

    // Lazy Loading:
    $('.lazy').Lazy({
        scrollDirection: 'vertical',
        effect: 'fadeIn',
        effectTime: 350,
        threshold: 200,
        onError: function(element) {
            console.log('error loading ' + element.data('src'));
        },
        afterLoad: function(element) {
            $(element).css("top", "0px");
        }
    });


    // Hamburger Menu:
    $(".hamburger").on("click", function() {
        var $this = $(this);
        var active = "is-active";

        if($this.hasClass(active)) {
            $this.removeClass(active);
        }
        else{
            $this.addClass(active);
        }
    });


});
