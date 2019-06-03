<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class vmc4Action extends vidiunAction
{
	const CURRENT_VMC_VERSION = 4;
    const HTML5_STUDIO_TAG = 'HTML5Studio';
    const STUDIO_V3_TAG = 'HTML5StudioV3';
    const PLAYER_V3_VERSIONS_TAG = 'playerV3Versions';
	const LIVE_ANALYTICS_UICONF_TAG = 'livea_player';
	const LIVE_DASHBOARD_UICONF_TAG = 'lived_player';
	
	private $confs = array();
	
	const SYSTEM_DEFAULT_PARTNER = 0;
	
	public function execute ( ) 
	{
		
		sfView::SUCCESS;

		/** check parameters and verify user is logged-in **/
		$this->vs = $this->getP ( "vmcvs" );
		if(!$this->vs)
		{
			// if vmcvs from cookie doesn't exist, try vs from REQUEST
			$this->vs = $this->getP('vs');
		}
		
		/** if no VS found, redirect to login page **/
		if (!$this->vs)
		{
			$this->redirect( "vmc/vmc" );
			die();
		}
		$vsObj = vSessionUtils::crackVs($this->vs);
		// Set partnerId from VS
		$this->partner_id = $vsObj->partner_id;

		// Check if the VMC can be framed
		$allowFrame = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ALLOW_FRAME, $this->partner_id);
		if(!$allowFrame) {
			header( 'X-Frame-Options: DENY' );
		}
		// Check for forced HTTPS
		$force_ssl = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ENFORCE_HTTPS, $this->partner_id);
		if( $force_ssl && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ) {
			header( "Location: " . infraRequestUtils::PROTOCOL_HTTPS . "://" . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] );
			die();
		}
		/** END - check parameters and verify user is logged-in **/
		
		/** Get array of allowed partners for the current user **/
		$allowedPartners = array();
		$this->full_name = "";
		$currentUser = vuserPeer::getVuserByPartnerAndUid($this->partner_id, $vsObj->user, true);
		if($currentUser) {
			$partners = myPartnerUtils::getPartnersArray($currentUser->getAllowedPartnerIds());
			foreach ($partners as $partner)
				$allowedPartners[] = array('id' => $partner->getId(), 'name' => $partner->getName());
				
			$this->full_name = $currentUser->getFullName();
		}
		$this->showChangeAccount = (count($allowedPartners) > 1 ) ? true : false;

		// Load partner
		$this->partner = $partner = PartnerPeer::retrieveByPK($this->partner_id);
		if (!$partner)
			VExternalErrors::dieError(VExternalErrors::PARTNER_NOT_FOUND);
		
		if (!$partner->validateApiAccessControl())
			VExternalErrors::dieError(VExternalErrors::SERVICE_ACCESS_CONTROL_RESTRICTED);
		
		vmcUtils::redirectPartnerToCorrectVmc($partner, $this->vs, null, null, null, self::CURRENT_VMC_VERSION);
		$this->templatePartnerId = $this->partner ? $this->partner->getTemplatePartnerId() : self::SYSTEM_DEFAULT_PARTNER;
		$ignoreEntrySeoLinks = PermissionPeer::isValidForPartner(PermissionName::FEATURE_IGNORE_ENTRY_SEO_LINKS, $this->partner_id);
		$useEmbedCodeProtocolHttps = PermissionPeer::isValidForPartner(PermissionName::FEATURE_EMBED_CODE_DEFAULT_PROTOCOL_HTTPS, $this->partner_id);
		$showFlashStudio = PermissionPeer::isValidForPartner(PermissionName::FEATURE_SHOW_FLASH_STUDIO, $this->partner_id);
		$showHTMLStudio = PermissionPeer::isValidForPartner(PermissionName::FEATURE_SHOW_HTML_STUDIO, $this->partner_id);
		$showStudioV3 = PermissionPeer::isValidForPartner(PermissionName::FEATURE_V3_STUDIO_PERMISSION, $this->partner_id);
		$deliveryTypes = $partner->getDeliveryTypes();
		$embedCodeTypes = $partner->getEmbedCodeTypes();
		$defaultDeliveryType = ($partner->getDefaultDeliveryType()) ? $partner->getDefaultDeliveryType() : 'http';
		$defaultEmbedCodeType = ($partner->getDefaultEmbedCodeType()) ? $partner->getDefaultEmbedCodeType() : 'auto';
		$this->previewEmbedV2 = PermissionPeer::isValidForPartner(PermissionName::FEATURE_PREVIEW_AND_EMBED_V2, $this->partner_id);
		
		/** set values for template **/
		$this->service_url = requestUtils::getRequestHost();
		$this->host = $this->stripProtocol( $this->service_url );
		$this->embed_host = $this->stripProtocol( myPartnerUtils::getHost($this->partner_id) );
		if (vConf::hasParam('cdn_api_host') && vConf::hasParam('www_host') && $this->host == vConf::get('cdn_api_host')) {
	        $this->host = vConf::get('www_host');
		}
		if($this->embed_host == vConf::get("www_host") && vConf::hasParam('cdn_api_host')) {
			$this->embed_host = vConf::get('cdn_api_host');
		}
		$this->embed_host_https = (vConf::hasParam('cdn_api_host_https')) ? vConf::get('cdn_api_host_https') : vConf::get('www_host');	

		$this->cdn_url = myPartnerUtils::getCdnHost($this->partner_id);
		$this->cdn_host = $this->stripProtocol( $this->cdn_url );
		$this->rtmp_host = vConf::get("rtmp_url");
		$this->flash_dir = $this->cdn_url . myContentStorage::getFSFlashRootPath ();

		/** set payingPartner flag **/
		$this->payingPartner = 'false';
		if($partner && $partner->getPartnerPackage() != PartnerPackages::PARTNER_PACKAGE_FREE)
		{
			$this->payingPartner = 'true';
			$ignoreSeoLinks = true;
		} else {
			$ignoreSeoLinks = $this->partner->getIgnoreSeoLinks();
		}

		/** get partner languae **/
		$language = null;
		if ($partner->getVMCLanguage())
			$language = $partner->getVMCLanguage();

		$first_login = $partner->getIsFirstLogin();
		if ($first_login === true)
		{
			$partner->setIsFirstLogin(false);
			$partner->save();
		}
		
		/** get logout url **/
		$logoutUrl = null; 
		if ($partner->getLogoutUrl())
			$logoutUrl = $partner->getLogoutUrl();
		
		$this->vmc_swf_version = vConf::get('vmc_version');

		$akamaiEdgeServerIpURL = null;
		if( vConf::hasParam('akamai_edge_server_ip_url') ) {
			$akamaiEdgeServerIpURL = vConf::get('akamai_edge_server_ip_url');
		}
		
	/** uiconf listing work **/
		/** fill $confs with all uiconf objects for all modules **/
		$vmcGeneralUiConf = vmcUtils::getAllVMCUiconfs('vmc',   $this->vmc_swf_version, self::SYSTEM_DEFAULT_PARTNER);
		$vmcGeneralTemplateUiConf = vmcUtils::getAllVMCUiconfs('vmc',   $this->vmc_swf_version, $this->templatePartnerId);

		
		/** for each module, create separated lists of its uiconf, for each need **/
		/** vmc general uiconfs **/
		$this->vmc_general = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_vmcgeneral", false, $vmcGeneralUiConf);
		$this->vmc_permissions = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_vmcpermissions", false, $vmcGeneralUiConf);
		/** P&E players: **/
		//$this->content_uiconfs_previewembed = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_previewembed", true, $vmcGeneralUiConf);
		//$this->content_uiconfs_previewembed_list = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_previewembed_list", true, $vmcGeneralUiConf);
		$this->content_uiconfs_flavorpreview = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_flavorpreview", false, $vmcGeneralUiConf);

		/* VCW uiconfs */
		$this->content_uiconfs_upload_webcam = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_uploadWebCam", false, $vmcGeneralUiConf);
		$this->content_uiconfs_upload_import = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_uploadImport", false, $vmcGeneralUiConf);

		$this->content_uiconds_clipapp_vdp = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_vdpClipApp", false, $vmcGeneralUiConf);
		$this->content_uiconds_clipapp_vclip = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_vClipClipApp", false, $vmcGeneralUiConf);
		
		$this->studioUiConf = uiConfPeer::getUiconfByTagAndVersion(self::HTML5_STUDIO_TAG, vConf::get("studio_version"));
		$this->content_uiconfs_studio_v2 = isset($this->studioUiConf) ? array_values($this->studioUiConf) : null;
		$this->content_uiconf_studio_v2 = (is_array($this->content_uiconfs_studio_v2) && reset($this->content_uiconfs_studio_v2)) ? reset($this->content_uiconfs_studio_v2) : null;

		$this->studioV3UiConf = uiConfPeer::getUiconfByTagAndVersion(self::STUDIO_V3_TAG, vConf::get("studio_v3_version"));
		$this->content_uiconfs_studio_v3 = isset($this->studioV3UiConf) ? array_values($this->studioV3UiConf) : null;
		$this->content_uiconf_studio_v3 = (is_array($this->content_uiconfs_studio_v3) && reset($this->content_uiconfs_studio_v3)) ? reset($this->content_uiconfs_studio_v3) : null;

		$this->playerV3VersionsUiConf = uiConfPeer::getUiconfByTagAndVersion(self::PLAYER_V3_VERSIONS_TAG, "latest");
		$this->content_uiconfs_player_v3_versions = isset($this->playerV3VersionsUiConf) ? array_values($this->playerV3VersionsUiConf) : null;
		$this->content_uiconf_player_v3_versions = (is_array($this->content_uiconfs_player_v3_versions) && reset($this->content_uiconfs_player_v3_versions)) ? reset($this->content_uiconfs_player_v3_versions) : null;

		$this->liveAUiConf = uiConfPeer::getUiconfByTagAndVersion(self::LIVE_ANALYTICS_UICONF_TAG, vConf::get("liveanalytics_version"));
		$this->content_uiconfs_livea = isset($this->liveAUiConf) ? array_values($this->liveAUiConf) : null;
		$this->content_uiconf_livea = (is_array($this->content_uiconfs_livea) && reset($this->content_uiconfs_livea)) ? reset($this->content_uiconfs_livea) : null;
		
		$this->liveDUiConf = uiConfPeer::getUiconfByTagAndVersion(self::LIVE_DASHBOARD_UICONF_TAG, vConf::get("live_dashboard_version"));
		$this->content_uiconfs_lived = isset($this->liveDUiConf) ? array_values($this->liveDUiConf) : null;
		$this->content_uiconf_lived = (is_array($this->content_uiconfs_lived) && reset($this->content_uiconfs_lived)) ? reset($this->content_uiconfs_lived) : null;

		$vmcVars = array(
			'vmc_version'				=> $this->vmc_swf_version,
			'vmc_general_uiconf'		=> $this->vmc_general->getId(),
			'vmc_permissions_uiconf'	=> $this->vmc_permissions->getId(),
			'allowed_partners'			=> $allowedPartners,
			'vmc_secured'				=> (bool) vConf::get("vmc_secured_login"),
			'enableLanguageMenu'		=> true,
			'service_url'				=> $this->service_url,
			'host'						=> $this->host,
			'cdn_host'					=> $this->cdn_host,
			'rtmp_host'					=> $this->rtmp_host,
			'embed_host'				=> $this->embed_host,
			'embed_host_https'			=> $this->embed_host_https,
			'flash_dir'					=> $this->flash_dir,
			'getuiconfs_url'			=> '/index.php/vmc/getuiconfs',
			'terms_of_use'				=> vConf::get('terms_of_use_uri'),
			'vs'						=> $this->vs,
			'partner_id'				=> $this->partner_id,
			'first_login'				=> (bool) $first_login,
			'whitelabel'				=> $this->templatePartnerId,
			'ignore_seo_links'			=> (bool) $ignoreSeoLinks,
			'ignore_entry_seo'			=> (bool) $ignoreEntrySeoLinks,
			'embed_code_protocol_https'	=> (bool) $useEmbedCodeProtocolHttps,
			'delivery_types'			=> $deliveryTypes,
			'embed_code_types'			=> $embedCodeTypes,
			'default_delivery_type'		=> $defaultDeliveryType,
			'default_embed_code_type'	=> $defaultEmbedCodeType,
			'vcw_webcam_uiconf'			=> $this->content_uiconfs_upload_webcam->getId(),
			'vcw_import_uiconf'			=> $this->content_uiconfs_upload_import->getId(),
			'default_vdp'				=> array(
				'id'					=> $this->content_uiconfs_flavorpreview->getId(),
				'height'				=> $this->content_uiconfs_flavorpreview->getHeight(),
				'width'					=> $this->content_uiconfs_flavorpreview->getWidth(),
				'swf_version'			=> $this->content_uiconfs_flavorpreview->getswfUrlVersion(),
			),
			'clipapp'					=> array(
				'version'				=> vConf::get("clipapp_version"),
				'vdp'					=> $this->content_uiconds_clipapp_vdp->getId(),
				'vclip'					=> $this->content_uiconds_clipapp_vclip->getId(),
			),
			'studio'					=> array(
                'version'				=> vConf::get("studio_version"),
                'uiConfID'				=> isset($this->content_uiconf_studio_v2) ? $this->content_uiconf_studio_v2->getId() : '',
                'config'				=> isset($this->content_uiconf_studio_v2) ? $this->content_uiconf_studio_v2->getConfig() : '',
                'showFlashStudio'		=> $showFlashStudio,
                'showHTMLStudio'		=> $showHTMLStudio,
                'showStudioV3'		    => $showStudioV3,
                'html5_version'		    => vConf::get("html5_version")
            ),
            'studioV3'					=> array(
                'version'				=> vConf::get("studio_v3_version"),
                'uiConfID'				=> isset($this->content_uiconf_studio_v3) ? $this->content_uiconf_studio_v3->getId() : '',
                'config'				=> isset($this->content_uiconf_studio_v3) ? $this->content_uiconf_studio_v3->getConfig() : '',
                'playerVersionsMap'		=> isset($this->content_uiconf_player_v3_versions) ? $this->content_uiconf_player_v3_versions->getConfig() : '',
                'showFlashStudio'		=> $showFlashStudio,
                'showHTMLStudio'		=> $showHTMLStudio,
                'showStudioV3'		    => $showStudioV3,
                'html5_version'		    => vConf::get("html5_version"),
                'publisherEnvType'		=> isset($this->partner) ? $this->partner->getPublisherEnvironmentType() : ''
            ),
			'liveanalytics'					=> array(
                'version'				=> vConf::get("liveanalytics_version"),
                'player_id'				=> isset($this->content_uiconf_livea) ? $this->content_uiconf_livea->getId() : '',
					
				'map_zoom_levels' => vConf::hasParam ("map_zoom_levels") ? vConf::get ("map_zoom_levels") : '',
			    'map_urls' => vConf::hasParam ("cdn_static_hosts") ? array_map(function($s) {return "$s/content/static/maps/v1";}, vConf::get ("cdn_static_hosts")) : '',
            ),
			'usagedashboard'			=> array(
				'version'				=> vConf::get("usagedashboard_version"),
			),
			'liveDashboard'             => array(
                'version'				=> vConf::get("live_dashboard_version"),
				'uiConfId'				=> isset($this->content_uiconf_lived) ? $this->content_uiconf_lived->getId() : '',
            ),
			'disable_analytics'			=> (bool) vConf::get("vmc_disable_analytics"),
			'google_analytics_account'	=> vConf::get("ga_account"),
			'language'					=> $language,
			'logoutUrl'					=> $logoutUrl,
			'allowFrame'				=> (bool) $allowFrame,
			'akamaiEdgeServerIpURL'		=> $akamaiEdgeServerIpURL,
			'logoUrl' 					=> vmcUtils::getWhitelabelData( $partner, 'logo_url'),
			'supportUrl' 				=> vmcUtils::getWhitelabelData( $partner, 'support_url'),
		);
		
		$this->vmcVars = $vmcVars;
	}

	private function stripProtocol( $url )
	{
		$url_data = parse_url( $url );
		if( $url_data !== false ){
			$port = (isset($url_data['port'])) ? ':' . $url_data['port'] : '';
			return $url_data['host'] . $port;
		} else {
			return $url;
		}
	}
    
}
