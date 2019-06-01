<style>
body { background-color:#272929 !important; background-image:none !important;}
div#login { width:500px; margin: 0 auto; text-align:center;}
</style>
<link rel="stylesheet" type="text/css" media="screen" href="/lib/css/vmc5.css" />
<div id="vmcHeader">
	<?php if( $logoUrl ) { ?>
	<img src="<?php echo $logoUrl; ?>" />
	<?php } else { ?>
	<img src="/lib/images/vmc/logo_vmc.png" alt="Vidiun CMS" />
	<?php } ?>
	<div id="langIcon" style="display: none"></div>
	<div id="user_links" style="right: 36px">
    	<a href="/content/docs/pdf/VMC_User_Manual.pdf" target="_blank">User Manual</a>
	</div> 
</div><!-- end vmcHeader -->

<div id="langMenu"></div>

<div id="login">
	<div id="notSupported">Thank you for your logging into the Vidiun Management Console.<br />The VMC is no longer supported in Internet Explorer 7.<br />Please upgrade your Internet Explorer to a higher version or browse to the VMC from another browser.</div>
    <div id="login_swf"><img src="/lib/images/vmc/flash.jpg" alt="Install Flash Player" /><span>You must have flash installed. <a href="http://get.adobe.com/flashplayer/" target="_blank">click here to download</a></span></div>
</div>

<script type="text/javascript">
// Prevent the page to be framed
if(top != window) { top.location = window.location; }
// Options
var options = {
	secureLogin: <?php echo ($securedLogin) ? 'true' : 'false'; ?>,
	enableLanguageMenu: "<?php echo 'true'; ?>",
	swfUrl: "<?php echo $swfUrl; ?>",
	flashVars: {
		host: "<?php echo $www_host; ?>",
		displayErrorFromServer: "<?php echo ($displayErrorFromServer)? 'true': 'false'; ?>",
		visibleSignup: "<?php echo (vConf::get('vmc_login_show_signup_link'))? 'true': 'false'; ?>",
		hashKey: "<?php echo (isset($setPassHashKey) && $setPassHashKey) ? $setPassHashKey : ''; ?>",
		errorCode: "<?php echo (isset($hashKeyErrorCode) && $hashKeyErrorCode) ? $hashKeyErrorCode : ''; ?>"
	}
};
</script>
<script src="/lib/js/vmc/6.0.10/langMenu.min.js"></script>
<script type="text/javascript" src="/lib/js/vmc.login.js"></script>