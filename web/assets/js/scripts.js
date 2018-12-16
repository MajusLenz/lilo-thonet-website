$(function() {

    $(function() {
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
    });

});