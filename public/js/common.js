$(document).ready(function() {
    // enable tabs
    $('.tabs').tabs();

    // enable twipsys
    $("a[rel=twipsy]").twipsy({
        live: true,
        placement: 'below'
    });
    /*
    $('a[rel="external"]').live('click', function(e) {
        e.preventDefault();
        window.open($(e.target).attr("href"));
    });

    var container = $("#home_killspage"),
        runtimePagerCache = {};

    $('a.pager').live('click', function(e){
        e.preventDefault();

        var url = $(e.target).parent().attr("href") + 'xhr/';

        if(runtimePagerCache[url])
        {
            container.html(runtimePagerCache[url]);
        } else {
            $.ajax({
               url: url,
               method: "GET",
               success: function(response) {
                   runtimePagerCache[url] = response;
                   container.html(response);
               }
            });
        }
    });*/
});