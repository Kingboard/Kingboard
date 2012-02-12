$(document).ready(function() {
    // enable tabs
    $('.tabs').tabs();

    // enable twipsys
    $("a[rel=twipsy]").twipsy({
        live: true,
        placement: 'below'
    });

    $('.dropdown-toggle').dropdown();
});