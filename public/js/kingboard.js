// Typeahead search functions..
$(function(){
	$("#searchbox").typeahead({
		source: function(typeahead, query){
 
			//clear the old rate limiter
			clearTimeout($('#typeahead').data('limiter'));
 
			//wrap the ajax stuff into a closure
			var ajax_request = function()
			{
				$.ajax({
					url: '/searchautocomplete/',
					type: 'GET',
					data: '/' + query,
					dataType: 'JSON',
					async: true,
					success: function(data){typeahead.process(data);}
				});
			}
 
			//start the new timer
			$('#typeahead').data('limiter', setTimeout(ajax_request, 500));
		},
		onselect: function(obj) {
			$('form[name="search"]').submit();
		}
	});
});