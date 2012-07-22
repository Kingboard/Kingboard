$(document).ready(function() {
    // enable tabs
    //$('.tabs').tab('show');

    $("body").tooltip({
        selector: "a[rel=twipsy]",
        placement: "bottom"
    });
    $('.dropdown-toggle').dropdown();


    $("#searchbox").typeahead({
        source: function(typeahead, query){

            //clear the old rate limiter
            clearTimeout($('#typeahead').data('limiter'));

            //wrap the ajax stuff into a closure
            var ajax_request = function()
            {
                $.ajax({
                    url: '/autocomplete/',
                    type: 'GET',
                    data: '/' + query,
                    dataType: 'JSON',
                    async: true,
                    success: function(data){typeahead.process(data);}
                });
            }

            //start the new timer
            $('#typeahead').data('limiter', setTimeout(ajax_request, 250));
        },
        onselect: function(obj) {
            $('form[name="search"]').submit();
        }
   });
});
