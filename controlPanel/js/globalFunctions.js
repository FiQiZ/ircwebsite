function hasClass(element, cls) {
	return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
}

function elementIdExists(element) {
	return !!document.getElementById(element);
}

function ObjectLength( object ) {
	var length = 0;
	for( var key in object ) {
		if( object.hasOwnProperty(key) ) {
			++length;
		}
	}
	return length;
}

function toTitleCase(str)
{
	return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function isChecked(element) {
	return $("#" + element).is(":checked");
}

function getDate(element) {
	return $("#" + element).pickadate('picker').get('select', 'yyyy-mm-dd');
}

function formatDate(dateString, format) {
	var dateFormat = "DD/MM/YYYY";

	if(format != undefined) {
		dateFormat = format;
	}

	if(dateString == null || dateString == undefined || dateString == "") {
		return "N/A";
	}
	else {
		return moment(dateString).format(dateFormat)
	}
}

function goToByScroll(id){
	$('html, body').animate({
		scrollTop: $("#" + id).offset().top
	}, 'slow');
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}