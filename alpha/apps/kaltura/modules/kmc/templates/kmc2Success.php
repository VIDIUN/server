<?php
$jw_license = null;
$jw_license = ($jw_license) ? "licensed" : "non-commercial";
$service_url = myPartnerUtils::getHost($partner_id);		/*** move to action ***/
$host = str_replace ( "http://" , "" , $service_url );
$cdn_url = myPartnerUtils::getCdnHost($partner_id);			/*** move to action ***/
$cdn_host = str_replace ( "http://" , "" , $cdn_url );

$flash_dir = $cdn_url . myContentStorage::getFSFlashRootPath ();	/*** move to action ***/
//$allow_reports = false;
$disableurlhashing = vConf::get('disable_url_hashing');
if ( !$allow_reports )
{
  $first_login = true;
}
if ( vConf::get('vmc_display_server_tab') )
{
  $support_url = '#support';
  $_SESSION['api_v3_login'] = true;
}
else
{
	$support_url = '/index.php/vmc/support?type=' . md5($payingPartner) . '&pid=' . $partner_id . '&email=' . $email;
//  if($payingPartner === 'true') // paying partner, not CE
//  {
//    $support_url = 'http://corp.vidiun.com/support/form/project/30/partnerId/'.(($host != 1)? '': $partner_id);
//    $support_url = 'http://tickets.vidiun.com/';
//  }
//  else // free partner - open modal
//  {
//    $support_url = '/index.php/vmc/support';
//  }
}
//if ( $host == "www.vidiun.com" ) $host = "1";
?>
<?php													/*** move to action ***/
	$uiconfs_array = array();
	foreach($playlist_uiconf_list as $uiconf)
	{
		$uiconf_array = array();
		$uiconf_array["id"] = $uiconf->getId();
		$uiconf_array["name"] = $uiconf->getName();
		$uiconf_array["width"] = $uiconf->getWidth();
		$uiconf_array["height"] = $uiconf->getHeight();
		$uiconf_array["swf_version"] = "v" . $uiconf->getswfUrlVersion();

		$uiconfs_array[] = $uiconf_array;
	}
	$ui_confs_playlist = json_encode(array_merge($uiconfs_array,$jw_uiconf_playlist));

	$uiconfs_array = array();
	foreach($player_uiconf_list as $uiconf)
	{
		$uiconf_array = array();
		$uiconf_array["id"] = $uiconf->getId();
		$uiconf_array["name"] = $uiconf->getName();
		$uiconf_array["width"] = $uiconf->getWidth();
		$uiconf_array["height"] = $uiconf->getHeight();
		$uiconf_array["swf_version"] = "v" . $uiconf->getswfUrlVersion();

		$uiconfs_array[] = $uiconf_array;
	}
	$ui_confs_player = json_encode(array_merge($uiconfs_array,$jw_uiconfs_array));
?>

<script type="text/javascript"> // move to vmc_js.php and include ?
	var vmc = {
		vars : {
			service_url		: "<?php echo $service_url; ?>",
			host			: "<?php echo $host; ?>",
			cdn_host		: "<?php echo $cdn_host; ?>",
			flash_dir		: "<?php echo $flash_dir ?>",
			createmix_url	: "<?php echo url_for('vmc/createmix'); ?>",
			getuiconfs_url	: "<?php echo url_for('vmc/getuiconfs'); ?>",
			terms_of_use	: "<?php echo vConf::get('terms_of_use_uri'); ?>",
			jw_swf			: "<?php echo $jw_license; ?>.swf",
			vs				: "<?php echo $vs; ?>",
			partner_id		: "<?php echo $partner_id; ?>",
			subp_id			: "<?php echo $subp_id; ?>",
			user_id			: "<?php echo $uid; ?>",
			screen_name		: "<?php echo $screen_name; ?>",
			email			: "<?php echo $email; ?>",
			first_login		: <?php echo ($first_login) ? "true" : "false"; ?>,
			paying_partner	: "<?php echo $payingPartner; ?>",
			show_usage		: <?php echo (vConf::get("vmc_account_show_usage"))? "true" : "false"; ?>,
			vse_uiconf		: "<?php echo $simple_editor; ?>", // add "id"
			vae_uiconf		: "<?php echo $advanced_editor; ?>", // add "id"
			enable_live		: "false",
			default_vdp	: { 
					uiconf_id	: 48507, // 1001639 is bad
					width		: 400,
					height		: 333,
					swf_version : "v3.1.1"
			},
			versions		: {
					dashboard	:	"<?php echo $vmc_dashboard_version ?>",
					content		:	"<?php echo $vmc_content_version ?>",
					appstudio	:	"<?php echo $vmc_appstudio_version ?>",
					account		:	"<?php echo $vmc_account_version ?>", // "Settings" tab
					reports		:	"<?php echo $vmc_rna_version ?>"
			},
			next_state			: { module : "dashboard", subtab : "default" },
			disableurlhashing	: "<?php echo $disableurlhashing; ?>",
			players_list		: <?php echo $ui_confs_player; ?>,
			playlists_list		: <?php echo $ui_confs_playlist; ?>
		}
	}

	//	var ui_confs_playlist = <?php //echo $ui_confs_playlist; ?>;
	// var ui_confs_player = vmc.vars.players_list;

</script>

	<div id="vmcHeader">
	<?php if (!$templatePartnerId) { ?>
     <!-- <img src="<?php //echo $service_url; ?>/lib/images/vmc/logo_vmc.jpg" alt="Vidiun Management Console" /> -->
	<?php } ?>
		<div id="logo"></div>
     <ul>
      <li><a id="Dashboard" href="<?php echo $service_url; ?>/index.php/vmc/vmc2#dashboard|''"><span>Dashboard</span></a></li>
      <li><a id="Content" href="<?php echo $service_url; ?>/index.php/vmc/vmc2#content|Manage"><span>Content</span></a></li>
     <?php if ( vConf::get ( "vmc_display_customize_tab" ) && !$templatePartnerId) { ?>
	  <li><a id="Appstudio" href="<?php echo $service_url; ?>/index.php/vmc/vmc2#appstudio|''"><span>Studio</span></a></li>
	 <?php } ?>
	 <?php if ( vConf::get ( "vmc_display_account_tab" ) ) { ?>
      <li><a id="Settings" href="<?php echo $service_url; ?>/index.php/vmc/vmc2#settings|Account Settings"><span>Settings</span></a></li>
	 <?php } ?>
	 <?php if ( vConf::get ( "vmc_display_server_tab" ) ) { ?>
      <li><a id="server" href="<?php echo $service_url; ?>/api_v3/system/batchwatch.php" target="_server"><span>Server</span></a></li>
	 <?php } ?>
	 <?php if ( vConf::get ( "vmc_display_developer_tab" ) ) { ?>
      <li><a id="developer" href="<?php echo $service_url; ?>/api_v3/testme/index.php"><span>Developer</span></a></li>
	 <?php } ?>
	 <?php if ($allow_reports) { ?>
	 <li><a id="Analytics" href="<?php echo $service_url; ?>/index.php/vmc/vmc2#reports|Bandwidth Usage Reports"><span>Analytics</span></a></li>
	 <?php } ?>
<!--	 <li><a id="Advertising" href="#"><span>Advertising</span></a></li>-->
	 </ul>

     <div id="user_links">
      <span>Hi <?php echo $screen_name ?></span><br />
      <?php if ($templatePartnerId) { ?>
      <a id="Logout" href="#login">Logout</a>
      <?php } else { ?>
      <a id="Quickstart Guide" href="<?php echo $service_url ?>/lib/pdf/VMC_Quick_Start_Guide.pdf" target="_blank">Quickstart Guide</a> &nbsp; | &nbsp;
	  <a id="Logout" href="#logout">Logout</a> &nbsp; | &nbsp;
	  <a id="Support" href="<?php echo $support_url; ?>" target="_blank">Support</a> <!-- @todo: !!! -->
      <?php } ?>
	 </div>
	</div><!-- vmcHeader -->

	<div id="main">
		<div id="flash_wrap" class="flash_wrap">
			<div id="vcms"></div>
		</div><!-- flash_wrap -->
        <div id="server_wrap">
         <iframe frameborder="0" id="server_frame" height="100%" width="100%"></iframe>
        </div> <!-- server_wrap -->
	</div><!-- main -->
<script type="text/javascript" src="/lib/js/vmc.js"></script>