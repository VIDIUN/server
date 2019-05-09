// Get the VMC base url based on current location
var baseUrl = (options.secureLogin) ? 'https:' : window.location.protocol;
	baseUrl += '//' + window.location.hostname;
	baseUrl += (window.location.port) ? ':' + window.location.port : '';

function loginF( remMe, partner_id, subp_id, uid, vs , screen_name, email ) {

	// Extlogin URL
	var hash = window.location.hash || ''; 
	var url = baseUrl + '/index.php/vmc/extlogin' + hash;

	// Setup input fields
	var vs_input = $('<input />').attr({
		'type': 'hidden',
		'name': 'vs',
		'value': vs
	});
	var partner_id_input = $('<input />').attr({
		'type': 'hidden',
		'name': 'partner_id',
		'value': partner_id // grab the selected partner id
	});

	var $form = $('<form />')
				.attr({
					'action': url, 
					'method': 'post',
					'style': 'display: none'
				})
				.append( vs_input, partner_id_input );

	// Submit the form
	$('body').append( $form );
	$form[0].submit();	
}

function gotoSignup() {
	window.location = baseUrl + "/index.php/vmc/signup";
}

$(function() {
	// Show login form
	var flashVars = {
		loginF: "loginF",
		closeF: "closeLoginF",
		urchinNumber: "UA-12055206-1",
		srvurl: "api_v3/index.php",
	    language: window.lang
	}

	$.extend( flashVars, options.flashVars );

	var params = {
		allowscriptaccess: "always",
		allownetworking: "all",
		bgcolor: "#272929",
		quality: "high",
		wmode: "window" ,
		movie: options.swfUrl
	};
	swfobject.embedSWF(options.swfUrl, "login_swf", "384", "350", "10.0.0", baseUrl + "/expressInstall.swf", flashVars, params);
});