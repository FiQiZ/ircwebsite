/*jslint browser: true*/
/*global $, jQuery, alert*/

$(document).ready(function () {
	
	// Cache the Window object
    "use strict";
	var $window = $(window);
	
	// Parallax Backgrounds
	// Tutorial: http://code.tutsplus.com/tutorials/a-simple-parallax-scrolling-technique--net-27641
	
	$('section[data-type="background"]').each(function () {
        
        var $bgobj = $(this); // assigning the object
		
		$window.scroll(function () {
		
			// Scroll the background at var speed
			// the yPos is a negative value because we're scrolling it UP!								
			var yPos = -($window.scrollTop() / $bgobj.data('speed')),
                coords = '50%' + yPos + 'px';
			
			// Put together our final background position
			//var coords = '50%' + yPos + 'px';
			
			// Move the background
			$bgobj.css({ backgroundPosition: coords });
			
		}); // end window scroll
	});
	
});

// handle links with @href started with '#' only
$(document).on('click', 'a[href^="#"]', function (e) {
    // target element id
    "use strict";
    var id = $(this).attr('href'),
        $id = $(id),
        pos = $id.offset().top;
    
    // target element
    // var $id = $(id);
    if ($id.length === 0) {
        return;
    }
    
    // prevent standard hash navigation (avoid blinking in IE)
    e.preventDefault();
    
    // top position relative to the document
    //var pos = $id.offset().top;
    
    // animated top scrolling
    $('body, html').animate({scrollTop: pos});
});