$(document).ready(function() {
    
   $(document).click(function(e) {
       var target = e.target;
       if (target.rel && target.rel == "external") {
           e.preventDefault();
           window.open($(target).attr("href"));
       }
   });
   
   var container = $("#home_killspage"),
       runtimePagerCache = {};
   if (container.length) {
      container.click(function(e) {
           if (e.target.className == "pager") {
               e.preventDefault();
               var url = $(e.target).attr("href") + 'xhr/';
               if (runtimePagerCache[url]) {
                   container.html(runtimePagerCache[url]);
               }
               else {
                   $.ajax({
                      url: url,
                      method: "GET",
                      success: function(response) {
                          runtimePagerCache[url] = response;
                          container.html(response);
                      }
                   });
               }
           }
       });
   }
});