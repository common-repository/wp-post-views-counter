(function($) {
    jQuery(document).ready(function($){
		var data = {
			'action': 'get_thea_post_count',
			'id': TheaScript.id
		};
   		$.post(TheaScript.TheaAjaxUrl, data, function(response) {
			$('.get_thea_count_number.thea-number').html(response);
		});
		var data1 = {
			'action': 'get_thea_uniq_post_count',
			'id': TheaScript.id
		};
		$.post(TheaScript.TheaAjaxUrl, data1, function(response) {
			
			$('.get_thea_count_number.thea-uniq-number').html(response);
		});
	});
})(jQuery);