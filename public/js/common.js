$(document).ready(function() {
    // enable tabs
    //$('.tabs').tab('show');

    $("body").tooltip({
        selector: "a[rel=twipsy]",
        placement: "bottom"
    });
    $('.dropdown-toggle').dropdown();

    $('#searchbox').typeahead(
      {
          name: 'search',
          remote: '/autocomplete/search/%QUERY'
      }
    ).bind("typeahead:selected",
            function() {
                $('#search').submit();
            }
    );

    $('#searchbox').keypress(function (e) {
      if (e.which == 13) {
        $('#search').submit();
        return false;
      }
    });
});
