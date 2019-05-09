<?php
$service_url = requestUtils::getHost();
$host = str_replace ( "http://" , "" , $service_url );
if ( $host == "www.vidiun.com" ) $host = "1";

$www_host = vConf::get('www_host');
if (vConf::get('vmc_secured_login')) {
	$flash_dir = 'https://';
}
else {
	$flash_dir = 'http://';
}
$flash_dir .= $www_host .'/'. myContentStorage::getFSFlashRootPath ();

$beta_str = $beta ? "/beta/{$beta}" : "";
?>
<script language="JavaScript" type="text/javascript">
<!--
// -----------------------------------------------------------------------------
var _partner_id, _subp_id, _uid;

function loginF( remMe , partner_id , subp_id ,  uid  ,  vs , screen_name , email  )
{
	var has_cookie = false;
	if ( partner_id == null )
	{
		partner_id = getCookie ( "varpid" );
		subp_id = getCookie ( "varsubpid" );
		uid = getCookie ( "varuid" );
		vs = getCookie ( "vplvs" );
		screen_name = getCookie ("varscreen_name" );
		// if any of the required params is null - return false and the login page will be displayed
		if ( empty(partner_id) || empty(subp_id) || empty(uid) || empty(vs) ) return false;
		
		has_cookie = true;
		
	}
	else
	{
	}	
//	alert( partner_id + " | " +  subp_id + " | " +   uid + " | " + vs + " | " + remMe);
	_partner_id = partner_id;
	_subp_id = subp_id;
	_uid = uid;
	path = '/';

	if ( remMe ) exp = 86400; // one day in seconds
	else exp = 10; // set the cookies to expire immediately
	if (!has_cookie)
	{
		setCookie ( "varpid" , partner_id , exp, path);
		setCookie ( "varsubpid" , subp_id , exp, path);
		setCookie ( "varuid" , uid , exp, path);
		setCookie ( "vplvs" , vs , exp, path);
		setCookie ( "varscreen_name" , screen_name , exp, path);
	}
	url = "<?php echo $service_url ?>/index.php/vmc/varpartnerlist<?php echo $beta_str ?>?partner_id=" + partner_id + "&subp_id=" + subp_id + "&uid=" + 
		uid + "&vs=" + vs + "&screen_name=" + screen_name + "&email=" + email  ;
//	alert ( url );
	window.location = url;

	// TODO - send by post using form1
	return true;			
}

function closeLoginF()
{
//	alert('closeLoginF');
}

function gotoSignup()
{
	window.location = "<?php echo $service_url ?>/index.php/vmc/signup";
}

// -->
</script>

<style>
#vmcHeader img { width:162px; height: 32px; }
   body { background-color:#272929 !important; background-image:none !important;}
   div.loginDesc { text-align:center; font-size:16px; font-weight:bold; color:white;}
#login { width:458px; }
#login .wrapper { padding-left:50px; width:358px; }   
 /*
body { background-image:none !important; }
 h1 { color: #272929; }
 div#wrap{ background-color:#9FCBFF; }
 div.login { background-color:#9FCBFF; }
 */
</style>

<form id="form1" action="<?php echo $service_url ?>/index.php/vmc/varpartnerlist<?php echo $beta_str ?>" method="post">
	<input type="hidden" name="_partner_id">
	<input type="hidden" name="_subp_id">
	<input type="hidden" name="_uid">
	<input type="hidden" name="_vs">
</form>	

<div class="login">
	<div id="vmcHeader">
     <img src="<?php echo $service_url; ?>/lib/images/vmc/varpages_logo.png" alt="Vidiun CMS" />
	</div><!-- end vmcHeader -->
	<div id="login">
		<div class="loginDesc">
			Here you can login to your publisher management console and access your sub-publisher accounts	
		</div>
		
		<div class="wrapper">
			<div id="vidiun_flash_obj"></div>
		</div><!-- end wrapper -->
	</div><!-- end #login -->
</div>	


<script type="text/javascript">
	// attempt to login without params - see if there are cookies - the remMe is true so the expiry will continue 
	if ( !loginF ( null , null , null , null , true ) ) 
	{
		var flashVars = {
			loginF: "loginF" ,
			closeF: "closeLoginF" ,
			host: "<?php echo $www_host ?>",
			visibleSignup: "false",
			urchinNumber: "UA-12055206-1",
            srvurl: "api_v3/index.php"
		}
	
		var params = {
			allowscriptaccess: "always",
			allownetworking: "all",
			bgcolor: "#272929",
			quality: "high",
			wmode: "window" ,
			movie: "<?php echo $flash_dir ?>/vmc/login/<?php echo $vmc_login_version ?>/login.swf"
		};
		swfobject.embedSWF("<?php echo $flash_dir ?>/vmc/login/<?php echo $vmc_login_version ?>/login.swf", 
			"vidiun_flash_obj", "358", "350", "9.0.0", false, flashVars , params);
	}

</script>

