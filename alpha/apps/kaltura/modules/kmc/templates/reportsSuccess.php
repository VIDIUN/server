<?php
$service_url = requestUtils::getRequestHost();
$protocol = requestUtils::getRequestProtocol();
$host = str_replace ( "$protocol://" , "" , $service_url );
$cdn_host = str_replace ( "http://" , "" , myPartnerUtils::getCdnHost($partner_id) );
$vmc_content_version = 'v1.1.8';
$vmc_account_version = 'v1.1.6';
$vmc_appstudio_version = 'v1.2.0';
$vmc_rna_version = 'v1.0.3';

$flash_dir = $service_url . myContentStorage::getFSFlashRootPath ();

?>
<script>
sub_nav_tab = "";
current_module = 'reports';
var flashVars = {	
		'host' : "<?php echo $host ?>" , 
		'cdnhost' : "<?php echo $cdn_host ?>" ,
		'uid' : "<?php echo htmlspecialchars($uid) ?>" ,
		'partner_id' : "<?php echo htmlspecialchars($partner_id) ?>",
		'srvurl' : 'api_v3/index.php',
		'innerVdpVersion' : 'v2.5.2.30876',
		'vdpUrl' : "<?php echo $flash_dir ?>/vdp/v2.5.2.30792/vdp.swf",
	    'uiconfId' : '48500' ,
		'subp_id' : "<?php echo htmlspecialchars($subp_id) ?>" ,
		'vs' : "<?php echo htmlspecialchars($vs) ?>" ,
		'widget_id' : "_<?php echo htmlspecialchars($partner_id) ?>" ,
		'devFlag' : 'false' ,
		'serverPath' : "<?php echo $service_url; ?>"
		};
		
	var params = {
		allowscriptaccess: "always",
		allownetworking: "all",
		bgcolor: "#1B1E1F",
		bgcolor: "#1B1E1F",				
		quality: "high",
//		wmode: "opaque" ,
		movie: "<?php echo $flash_dir ?>/vmc/analytics//ReportsAndAnalytics.swf"
	};
	swfobject.embedSWF("<?php echo $flash_dir ?>/vmc/analytics/<?php echo $vmc_rna_version ?>/ReportsAndAnalytics.swf", 
		"vcms", "100%", "100%", "9.0.0", false, flashVars , params);	
		
function content_resize(){
   var w = $( window );
   var H = w.height(); 
   var W = w.width(); 
   $( '#flash_wrap' ).height(H-5);
  // $('#server_wrap iframe').height(H-38);
}		
</script>

<div id='flash_wrap'>
<div id='vcms'></div>
</div>

<script>
content_resize();

</script>

