// Typeahead search functions..
$(function(){
    $("#searchbox").typeahead({
        // html stuff
        source: function(typeahead, query){

            //clear the old rate limiter
            clearTimeout($("#typeahead").data("limiter"));

            //wrap the ajax stuff into a closure
            var ajax_request = function()
            {
                $.ajax({
                    url: "/autocomplete/",
                    type: "GET",
                    data: "/" + query,
                    dataType: "JSON",
                    async: true,
                    success: function(results){
                    typeahead.process(results);
                    }
                });
            }
            
            //start the new timer
            $("#typeahead").data("limiter", setTimeout(ajax_request, 250));
        },
        onselect: function(obj) {
            $('form[name="search"]').submit();
        },
        highlighter: function(item){
            return '<ul class="nav nav-list"><li>' + item + "</li></ul>";
        }
    });
});