(function($) {
    "use strict";

	function buildJsonURL(perPage){
	 
	    var jsonUrl = events_opt.jsonUrl;
	    if (typeof(perPage) != 'undefined' && perPage != null){
	        jsonUrl += '?per_page='+perPage;
	    }
	    return jsonUrl;
	}

	$('.recent-event-wrapper').each(function(){

	    // 1. Create all the required variables
	    var $this          = $(this),
	        termFilter     = $this.find('.term-filter'),
	        tagFilter     = $this.find('.tag-filter'),
	        recentEvent     = $this.find('.recent-event'),
	        layout         = (recentEvent.hasClass('grid')) ? 'grid' : 'list',
	        perPage        = termFilter.data('per-page'),
	        requestRunning = false;

	    // 2. Term filter click event
	    $this.find('a').on('click',function(e){
	    /* 
	        3. Prevent link default
	           Make sure that the previous AJAX request is not ranning at the moment
	           Set a new requestRunning
	    */
	    e.preventDefault(); 
	    if (requestRunning) {return;} 
	    requestRunning = true;

	    // 4. Remove current events from the events list to append requested events later
	    recentEvent.addClass('loading');
	    recentEvent.find('li').remove();

	    // 5. Collect current filter data and toggle active class

	    var currentFilter     = $(this),
	        currentFilterLink = currentFilter.attr('href'),
	        currentFilterID   = currentFilter.data('filter-id');

	    currentFilter.addClass('active').siblings().removeClass('active');

	    // 6. Build the json AJAX call URL
	    var jsonUrl = buildJsonURL(perPage);
	    // console.log($(this).parents('.tag-filter').length);
	    var taxonomy = $(this).parents('.tag-filter').length <= 0 ? 'event_type' : 'event_tag';
	    // console.log(taxonomy);
	    if (typeof(currentFilterID) != 'undefined' && currentFilterID != null){
	        jsonUrl += '&'+taxonomy+'='+currentFilterID;
	    }
	    	console.log(jsonUrl);
	    // 7. Send AJAX request
            $.ajax({
                dataType: 'json',
                url:jsonUrl
            })
            .done(function(response){

                // 8. If success loop with each responce object and create tuturial output
                var output = '';
                $.each(response,function(index,object){
                    output += '<li>';
                        output += '<img src="'+object.event_image_src+'" alt="'+object.title.rendered+'" />';
                        output +='<div class="event-content">';
                            output +='<div class="event_type">';
                                var eventCategories = object.event_type_attr;
                                for (var key in eventCategories) {
                                    output += '<a href="'+eventCategories[key][1]+'" title="'+eventCategories[key][0]+'" rel="tag">'+eventCategories[key][0]+'</a> ';
                                }
                            output +='</div>';
                            if ( '' != object.title.rendered ){
                                output +='<h4 class="event-title entry-title">';
                                    output += '<a href="'+object.link+'" title="'+object.title.rendered+'" rel="bookmark">';
                                        output += object.title.rendered;
                                    output += '</a>';
                                output +='</h4>';
                            }
                            if ( '' != object.excerpt.rendered && layout == 'grid'){
                                output +='<div class="event-excerpt">'+object.excerpt.rendered.replace(/(<([^>]+)>)/ig,"")+'</div>';
                            }
                            output +='<div class="event-tag">';
                                var eventTags = object.event_tag_attr;
                                for (var key in eventTags) {
                                    output += '<a href="'+eventTags[key][1]+'" title="'+eventTags[key][0]+'" rel="tag">'+eventTags[key][0]+'</a> ';
                                }
                            output +='</div>';
                        output +='</div>';
                    output += '</li>';
                });
                // 9. If output is ready append new event into the events list
                if (output.length) {
                    recentEvent.append(output);
                    recentEvent.removeClass('loading');
                }
            })
            .fail(function(response){
                // 10. If fail alert error message
                alert("Something went wront, can't fetch Events");
            })
            .always(function(response){
                // 11. Always reset the requestRunning to keep sending new AJAX requests
                requestRunning = false;
            });
            return false;
        });
	});
})(jQuery);