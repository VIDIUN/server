$(function() {
	if ($('iframe.auto-height') && $('iframe.auto-height').size() > 0) {
		autoSizeIframe();
		$(window).resize(autoSizeIframe);
	}
});

function autoSizeIframe(){
	var availableHeight = $(window).height();
	var takenHeight =  $('#vmcHeader').outerHeight(true) + $('#sub-header').outerHeight(true);
	var height = availableHeight - takenHeight;
	$('iframe.auto-height').height(height);
	$('#wrapper').height(height); // fixes weird scrolling
}
