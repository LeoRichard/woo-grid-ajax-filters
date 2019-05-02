(function($) {
    'use strict';

    jQuery(document).ready(function() {

    	//Load all posts
    	wag_ajax_get_productdata(-1);

    	$('.wag_taxonomy').on('click', function() {

      		var term_id = $(this).attr('data_id');

      		$(this).addClass('active').siblings().removeClass('active');
          $(this).parent().addClass('current').siblings().removeClass('current');

      		wag_ajax_get_productdata(term_id);
    	});


    	//ajax filter function
    	function wag_ajax_get_productdata( term_ID ) {

          var localCache = {
              data: {},
              remove: function (term_ID) {
                  delete localCache.data[term_ID];
              },
              exist: function (term_ID) {
                  return localCache.data.hasOwnProperty(term_ID) && localCache.data[term_ID] !== null;
              },
              get: function (term_ID) {
                  //console.log('Getting in cache for term:' + term_ID);
                  return localCache.data[term_ID];
              },
              set: function (term_ID, cachedData, callback) {
                  localCache.remove(term_ID);
                  localCache.data[term_ID] = cachedData;
                  if ($.isFunction(callback)) callback(cachedData);
              }
          };

        $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
            if (options.cache) {
                var complete  = originalOptions.complete || $.noop,
                     success  = originalOptions.success || $.noop;

                term_ID = originalOptions.data.term_ID;

                //remove jQuery cache as we have our own localCache
                options.cache = false;
                options.beforeSend = function () {
                      $('.wag-loader').show();
                      $('.wag-filter-result').css("opacity", 0.5);
                      if (localCache.exist(term_ID)) {
                          complete(localCache.get(term_ID));
                          success(localCache.get(term_ID));
                          return false;
                      }
                      return true;
                };
                options.complete = function (data) {
          			     $('.wag-loader').hide();
          			};
                options.success = function (data, textStatus) {
                     localCache.set(term_ID, data, success);
                };
            }
        });

    		$.ajax({
      			type: 'post',
      			url: wag_ajax_params.wag_ajax_url,
      			data: {
        				action: 'wag_filter_products',
        				wag_ajax_nonce: wag_ajax_params.wag_ajax_nonce,
        				term_ID: term_ID,
      			},
            cache: true,
      			beforeSend: function (data) {
        		    $('.wag-loader').show();
                $('.wag-filter-result').css("opacity", 0.5);
      			},
      			complete: function (data) {
      				  $('.wag-loader').hide();
      			},
      			success: function (data) {
      				  $('.wag-filter-result').fadeOut(300, function() {
      						$(this).html(data).fadeIn(300);
                  $(this).css("opacity", 1);
    			      });
      			},
      			error: function (data) {
        				console.log(data);
                $('.wag-filter-result').css("opacity", 1);
      			},
    		});
    	}


    });
})(jQuery);
