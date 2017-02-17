jQuery(document).ready(function() {
    /*
        Fullscreen background
    */
    // $.backstretch("assets/img/backgrounds/1.jpg");
    
    /*
        Login form validation
    */
    $('.login-form input[type="text"], input[type="email"], .login-form input[type="password"], .login-form textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.login-form').on('submit', function(e) {
    	$(this).find('input[type="text"], input[type="email"], input[type="password"], textarea').each(function(){
    		if( $(this).val() == "" ) {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    });
    
    /*
        Registration form validation
    */
    $('.registration-form input[type="text"], input[type="email"], input[type="password"], .registration-form textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.registration-form').on('submit', function(e) {
    	$(this).find('input[type="text"], input[type="email"], input[type="password"], textarea').not('.form-fax-no').each(function(){
    		if($(this).val() == "") {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    });
    
    /*
        Verification form validation
    */
    $('.verification-form input[type="text"], input[type="email"], input[type="password"], .verification-form textarea').on('focus', function() {
        $(this).removeClass('input-error');
    });
    
    $('.verification-form').on('submit', function(e) {
        $(this).find('input[type="text"], input[type="email"], input[type="password"], textarea').not('.form-fax-no').each(function(){
            if($(this).val() == "") {
                e.preventDefault();
                $(this).addClass('input-error');
            }
            else {
                $(this).removeClass('input-error');
            }
        });
    });
});
