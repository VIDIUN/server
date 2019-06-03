<?php
	$domain = $widget_host;
	if ( strpos ( $domain , "localhost"  ) !== false )		$host = 2;
	elseif ( strpos ( $domain , "viddev" ) !== false ) 		$host = 0;
	else													$host = 1;

	$swf_url = "/swf/ContributionWizard.swf";

    $lang = "en" ;
	$height = 360;
	$width = 680;
	$flashvars = 		'userId=' . $uid .
						'&sessionId=' . $vs. 
						'&partnerId=' . $partner_id .
						'&subPartnerId=' . $subp_id . 
						'&vshow_id=' . $vshow_id . 
						'&host=' . $host . //$domain; it's an enum
						'&afterAddentry=Vidiun.onAfterAddEntry' .
						'&close=Vidiun.onClose' .
						'&lang=' . $lang . 
						'&terms_of_use=http://www.vidiun.com/index.php/static/tandc' ;
	$str = "";
					
    $widget = '<object id="vidiun_contribution_wizard" type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="' . $height . '" width="' . $width . '" data="'.$domain. $swf_url . '">'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value=#000000 />'.
			'<param name="movie" value="'.$domain. $swf_url . '"/>'.
    		'<param name="flashVars" value="' . $flashvars . '" />' .
			'</object>';
?>

Vidiun = {
	initModalBox: function(){
		var objBody = document.getElementsByTagName("body").item(0);

		// create overlay div and hardcode some functional styles (aesthetic styles are in CSS file)
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','overlay');
		objOverlay.onclick = function(){ 
			Vidiun.hideModalBox(); 
			return false; 
		}
		objBody.appendChild(objOverlay, objBody.firstChild);
		
		// create modalbox div, same note about styles as above
		var objModalbox = document.createElement("div");
		objModalbox.setAttribute('id','modalbox');
		
		// create exit button, inside 'lightbox'
		var objCloseBtn = document.createElement("a");
		objCloseBtn.setAttribute('id','mbCloseBtn');
		objCloseBtn.setAttribute('href','#');
		objCloseBtn.onclick = function(){ 
			Vidiun.hideModalBox(); 
			return false; 
		}
		objModalbox.appendChild(objCloseBtn, objModalbox.firstChild);
		
		// create content div inside objModalbox
		var objModalboxContent = document.createElement("div");
		objModalboxContent.setAttribute('id','mbContent');
		objModalboxContent.innerHTML = '<?php echo $widget; ?>';
		objModalbox.appendChild(objModalboxContent, objModalbox.firstChild);
		
		objBody.appendChild(objModalbox, objOverlay.nextSibling);
	},
	
	hideModalBox: function () {
		document.getElementById('overlay').parentNode.removeChild( document.getElementById('overlay') );
		document.getElementById('modalbox').parentNode.removeChild( document.getElementById('modalbox') );
	},
	
	onAfterAddEntry: function () {
		setTimeout('Vidiun.hideModalBox()', 0);
	},
	
	onClose: function () {
		setTimeout('Vidiun.hideModalBox()', 0);
	},

	loadJSCssFile: function(filename,filetype){
		var fileref;
		if(filetype=='js'){
			fileref=document.createElement('script');
			fileref.setAttribute('type','text/javascript');
			fileref.setAttribute('src',filename);
		}
		else if(filetype=='css'){
			if (this.findStyleSheet(filename)) return false;
			var fileref=document.createElement('link');
			fileref.setAttribute('rel','stylesheet');
			fileref.setAttribute('type','text/css');
			fileref.setAttribute('href',filename);
		};
		if(typeof fileref!='undefined') document.getElementsByTagName('head')[0].appendChild(fileref);
	},

	/* check if the css file is already in the HEAD */
	findStyleSheet: function(styleSheetUrl){
		var sheets = document.styleSheets;
		for (var S = 0; S < sheets.length; S++){
			if (sheets[S].href.indexOf(styleSheetUrl) != -1)
			return true;
		}
	}
};

Vidiun.loadJSCssFile('/css/widget.css','css');

setTimeout('Vidiun.initModalBox()', 0);


