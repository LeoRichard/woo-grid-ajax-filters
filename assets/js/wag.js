(function($) {
    'use strict';

    jQuery(document).ready(function() {

    	//Load all posts
    	wag_ajax_get_productdata(-1);

    	$('.wag_taxonomy').on('click',function(){
    		var term_id = $(this).attr('data_id');
    		$(this).addClass('active').siblings().removeClass('active');
        $(this).parent().addClass('current').siblings().removeClass('current');

    		//console.log(term_id);
    		wag_ajax_get_productdata(term_id);

    	});

    	//ajax filter function
    	function wag_ajax_get_productdata(term_ID){
    		$.ajax({
    			type: 'post',
    			url: wag_ajax_params.wag_ajax_url,
    			data: {
    				action: 'wag_filter_products',
    				wag_ajax_nonce: wag_ajax_params.wag_ajax_nonce,
    				term_ID: term_ID,
    			},
    			beforeSend: function(data){
    				$('.wag-loader').show();
            $('.wag-filter-result').css("opacity", 0.5);
    			},
    			complete: function(data){
    				$('.wag-loader').hide();
    			},
    			success: function(data){
    				$('.wag-filter-result').fadeOut(300, function() {
  						$(this).html(data).fadeIn(300);
              $(this).css("opacity", 1);
					});
    			},
    			error: function(data){
    				console.log(data);
            $('.wag-filter-result').css("opacity", 1);
    			},

    		});
    	}


    });
})(jQuery);
