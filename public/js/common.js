$(document).ready(function() {
    
   $(document).click(function(e) {
       var target = e.target;
       if (target.rel && target.rel == "external") {
           e.preventDefault();
           window.open($(target).attr("href"));
       }
   });
   
   var container = $("#home_killspage");
   if (container.length) {
      container.click(function(e) {
          e.preventDefault();
           if (e.target.className == "pager") {
               e.preventDefault();
               var url = $(e.target).attr("href");
               $.ajax({
                  url: url + '&ajax=1',
                  method: "GET",
                  success: function(response) {
                      container.html(response);
                  }
               });
           }
       });
   }
});