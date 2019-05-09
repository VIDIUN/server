<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class vmc3Action extends vidiunAction
{
	const CURRENT_VMC_VERSION = 3;
	private $confs = array();
	
	const SYSTEM_DEFAULT_PARTNER = 0;
	
	public function execute ( ) 
	{
		
		sfView::SUCCESS;

	/** check parameters and verify user is logged-in **/
		$this->partner_id = $this->getP ( "pid" );
		$this->subp_id = $this->getP ( "subpid", ((int)$this->partner_id)*100 );
		$this->uid = $this->getP ( "uid" );
		$this->vs = $this->getP ( "vmcvs" );
		if(!$this->vs)
		{
			// if vmcvs from cookie doesn't exist, try vs from REQUEST
			$this->vs = $this->getP('vs');
		}
		$this->screen_name = $this->getP ( "screen_name" );
		$this->email = $this->getP ( "email" );


		/** if no VS found, redirect to login page **/
		if (!$this->vs)
		{
			$this->redirect( "vmc/vmc" );
			die();
		}
	/** END - check parameters and verify user is logged-in **/

	/** load partner from DB, and set templatePartnerId **/
		$this->partner = $partner = null;
		$this->templatePartnerId = self::SYSTEM_DEFAULT_PARTNER;
		if ($this->partner_id !== NULL)
		{
			$this->partner = $partner = PartnerPeer::retrieveByPK($this->partner_id);
			vmcUtils::redirectPartnerToCorrectVmc($partner, $this->vs, $this->uid, $this->screen_name, $this->email, self::CURRENT_VMC_VERSION);
			$this->templatePartnerId = $this->partner ? $this->partner->getTemplatePartnerId() : self::SYSTEM_DEFAULT_PARTNER;
		}
	/** END - load partner from DB, and set templatePartnerId **/

	/** set default flags **/
		$this->allow_reports = false;
		$this->payingPartner = 'false';
		$this->embed_code  = "";
		$this->enable_live_streaming = 'false';
		$this->vmc_enable_custom_data = 'false';
		$this->vdp508_players = array();
		$this->first_login = false;
		$this->enable_vast = 'false';
	/** END - set default flags **/
	
	/** set values for template **/
	$this->service_url = myPartnerUtils::getHost($this->partner_id);
	$this->host = str_replace ( "http://" , "" , $this->service_url );
	$this->cdn_url = myPartnerUtils::getCdnHost($this->partner_id);
	$this->cdn_host = str_replace ( "http://" , "" , $this->cdn_url );
	$this->rtmp_host = vConf::get("rtmp_url");
	$this->flash_dir = $this->cdn_url . myContentStorage::getFSFlashRootPath ();
		
	/** set embed_code value **/
		if ( $this->partner_id !== null )
		{
			$widget = widgetPeer::retrieveByPK( "_" . $this->partner_id );
			if ( $widget )
			{
				$this->embed_code = $widget->getWidgetHtml( "vidiun_player" );
				
				$ui_conf = $widget->getuiConf();
			}
		}
	/** END - set embed_code value **/

	/** set payingPartner flag **/
		if($partner && $partner->getPartnerPackage() != PartnerPackages::PARTNER_PACKAGE_FREE)
		{
			$this->payingPartner = 'true';
		}
	/** END - set payingPartner flag **/
		
	/** set enable_live_streaming flag **/
		if(vConf::get('vmc_content_enable_live_streaming') && $partner)
		{
			if ($partner->getLiveStreamEnabled())
			{
				$this->enable_live_streaming = 'true';
			}
		}
	/** END - set enable_live_streaming flag **/

	/** set enable_live_streaming flag **/
		if($partner && $partner->getEnableVast())
		{
			$this->enable_vast = 'true';
		}
	/** END - set enable_live_streaming flag **/
		
	/** set vmc_enable_custom_data flag **/
		$defaultPlugins = vConf::get('default_plugins');
		if(is_array($defaultPlugins) && in_array('MetadataPlugin', $defaultPlugins) && $partner)
		{
			if ($partner->getPluginEnabled('metadata') && $partner->getVmcVersion() == self::CURRENT_VMC_VERSION)
			{
				$this->vmc_enable_custom_data = 'true';
			}
		}
	/** END - set vmc_enable_custom_data flag **/

	/** set allow_reports flag **/
		// 2009-08-27 is the date we added ON2 to VMC trial account
		// TODO - should be depracated
		if(strtotime($partner->getCreatedAt()) >= strtotime('2009-08-27') ||
		   $partner->getEnableAnalyticsTab())
		{
			$this->allow_reports = true;
		}
		if($partner->getEnableAnalyticsTab())
		{
			$this->allow_reports = true;
		}
		// if the email is empty - it is an indication that the vidiun super user is logged in
		if ( !$this->email) $this->allow_reports = true;
	/** END - set allow_reports flag **/
	
	/** set first_login and jw_license flags **/
		if ($partner)
		{
			$this->first_login = $partner->getIsFirstLogin();
			if ($this->first_login === true)
			{
				$partner->setIsFirstLogin(false);
				$partner->save();
			}
			$this->jw_license = $partner->getLicensedJWPlayer();
		}
	/** END - set first_login and jw_license flags **/
		
	/** partner-specific: change VDP version for partners working with auto-moderaion **/
		// set content vdp version according to partner id
		$moderated_partners = array( 31079, 28575, 32774 );
		$this->content_vdp_version = 'v2.7.0';
		if(in_array($this->partner_id, $moderated_partners))
		{
			$this->content_vdp_version = 'v2.1.2.29057';
		}
	/** END - partner-specific: change VDP version for partners working with auto-moderaion **/
		
	/** applications versioning **/
		$this->vmc_content_version 	= vConf::get('vmc_content_version');
		$this->vmc_account_version 	= vConf::get('vmc_account_version');
		$this->vmc_appstudio_version 	= vConf::get('vmc_appstudio_version');
		$this->vmc_rna_version 		= vConf::get('vmc_rna_version');
		$this->vmc_dashboard_version 	= vConf::get('vmc_dashboard_version');
	/** END - applications versioning **/
		
	/** uiconf listing work **/
		/** fill $this->confs with all uiconf objects for all modules **/
		$contentSystemUiConfs = vmcUtils::getAllVMCUiconfs('content',   $this->vmc_content_version, self::SYSTEM_DEFAULT_PARTNER);
		$contentTemplateUiConfs = vmcUtils::getAllVMCUiconfs('content',   $this->vmc_content_version, $this->templatePartnerId);
		//$this->confs = vmcUtils::getAllVMCUiconfs('content',   $this->vmc_content_version, $this->templatePartnerId);
		$appstudioSystemUiConfs = vmcUtils::getAllVMCUiconfs('appstudio', $this->vmc_appstudio_version, self::SYSTEM_DEFAULT_PARTNER);
		$appstudioTemplateUiConfs = vmcUtils::getAllVMCUiconfs('appstudio', $this->vmc_appstudio_version, $this->templatePartnerId);
		//$this->confs = array_merge($this->confs, vmcUtils::getAllVMCUiconfs('appstudio', $this->vmc_appstudio_version, $this->templatePartnerId));
		$reportsSystemUiConfs = vmcUtils::getAllVMCUiconfs('reports',   $this->vmc_rna_version, self::SYSTEM_DEFAULT_PARTNER);
		$reportsTemplateUiConfs = vmcUtils::getAllVMCUiconfs('reports',   $this->vmc_rna_version, $this->templatePartnerId);
		//$this->confs = array_merge($this->confs, vmcUtils::getAllVMCUiconfs('reports',   $this->vmc_rna_version, $this->templatePartnerId));
		
		/** for each module, create separated lists of its uiconf, for each need **/
		/** content players: **/
		$this->content_uiconfs_previewembed = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_previewembed", true, $contentSystemUiConfs);
		$this->content_uiconfs_previewembed_list = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_previewembed_list", true, $contentSystemUiConfs);
		$this->content_uiconfs_moderation = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_moderation", false, $contentSystemUiConfs);
		$this->content_uiconfs_drilldown = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_drilldown", false, $contentSystemUiConfs);
		$this->content_uiconfs_flavorpreview = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_flavorpreview", false, $contentSystemUiConfs);
		$this->content_uiconfs_metadataview = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_metadataview", false, $contentSystemUiConfs);
		/** content VCW,VSE,VAE **/
		$this->content_uiconfs_upload = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_upload", false, $contentSystemUiConfs);
		$this->simple_editor = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_simpleedit", false, $contentSystemUiConfs);
		$this->advanced_editor = vmcUtils::find_confs_by_usage_tag($contentTemplateUiConfs, "content_advanceedit", false, $contentSystemUiConfs);
		
		/** appStudio templates uiconf **/
		$this->appstudio_uiconfs_templates = vmcUtils::find_confs_by_usage_tag($appstudioTemplateUiConfs, "appstudio_templates", false, $appstudioSystemUiConfs);
		
		/** reports drill-down player **/
		$this->reports_uiconfs_drilldown = vmcUtils::find_confs_by_usage_tag($reportsTemplateUiConfs, "reports_drilldown", false, $reportsSystemUiConfs);
		
		/** silverlight uiconfs **/
		$this->silverLightPlayerUiConfs = array();
		$this->silverLightPlaylistUiConfs = array();
		if($partner->getVmcVersion() == self::CURRENT_VMC_VERSION && $partner->getEnableSilverLight())
		{
			$this->silverLightPlayerUiConfs = vmcUtils::getSilverLightPlayerUiConfs('slp');
			$this->silverLightPlaylistUiConfs = vmcUtils::getSilverLightPlayerUiConfs('sll');
		}

		/** jw uiconfs **/
		$this->jw_uiconfs_array = kmcUtils::getJWPlayerUIConfs($this->partner_id);
		$this->jw_uiconf_playlist = kmcUtils::getJWPlaylistUIConfs($this->partner_id);
		
		/** 508 uicinfs **/
		if($partner->getVmcVersion() == self::CURRENT_VMC_VERSION && $partner->getEnable508Players())
		{
			$this->vdp508_players = vmcUtils::getPlayerUiconfsByTag('vdp508');
		}
		
		/** partner's preview&embed uiconfs **/
		$this->content_pne_partners_player = vmcUtils::getPartnersUiconfs($this->partner_id, 'player');
		$this->content_pne_partners_playlist = vmcUtils::getPartnersUiconfs($this->partner_id, 'playlist');
		
		/** appstudio: default entry and playlists **/
		$this->appStudioExampleEntry = $partner->getAppStudioExampleEntry();
		$appStudioExampleEntry = entryPeer::retrieveByPK($this->appStudioExampleEntry);
		if (!($appStudioExampleEntry && $appStudioExampleEntry->getDisplayInSearch() == mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK && $appStudioExampleEntry->getStatus()== entryStatus::READY &&	$appStudioExampleEntry->getType() == entryType::MEDIA_CLIP ))
			$this->appStudioExampleEntry = "_VMCLOGO1";
		
		$this->appStudioExamplePlayList0 = $partner->getAppStudioExamplePlayList0();
		$appStudioExamplePlayList0 = entryPeer::retrieveByPK($this->appStudioExamplePlayList0);		
		if (!($appStudioExamplePlayList0 && $appStudioExamplePlayList0->getStatus()== entryStatus::READY && $appStudioExamplePlayList0->getType() == entryType::PLAYLIST ))
			$this->appStudioExamplePlayList0 = "_VMCSPL1";
		
		$this->appStudioExamplePlayList1 = $partner->getAppStudioExamplePlayList1();
		$appStudioExamplePlayList1 = entryPeer::retrieveByPK($this->appStudioExamplePlayList1);
		if (!($appStudioExamplePlayList1 && $appStudioExamplePlayList1->getStatus()== entryStatus::READY && $appStudioExamplePlayList1->getType() == entryType::PLAYLIST ))
			$this->appStudioExamplePlayList1 = "_VMCSPL2";
		/** END - appstudio: default entry and playlists **/
		
	/** END - uiconf listing work **/
		
		/** get templateXmlUrl for whitelabeled partners **/
		$this->appstudio_templatesXmlUrl = $this->getAppStudioTemplatePath();
	}

	private function getAppStudioTemplatePath()
	{
		$template_partner_id = (isset($this->templatePartnerId))? $this->templatePartnerId: self::SYSTEM_DEFAULT_PARTNER;
		if (!$template_partner_id)
			return false;
	
		$c = new Criteria();
		$c->addAnd(uiConfPeer::PARTNER_ID, $template_partner_id );
		$c->addAnd ( uiConfPeer::STATUS , uiConf::UI_CONF_STATUS_READY );
		$c->addAnd ( uiConfPeer::OBJ_TYPE , uiConf::UI_CONF_TYPE_VMC_APP_STUDIO );
		$c->addAnd(uiConfPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK);
	
		$uiConf = uiConfPeer::doSelectOne($c);
		if ($uiConf)
		{
			$sync_key = $uiConf->getSyncKey( uiConf::FILE_SYNC_UICONF_SUB_TYPE_DATA );
			if ($sync_key)
			{
				$file_sync = vFileSyncUtils::getLocalFileSyncForKey( $sync_key , true );
				if ($file_sync)
				{
					return "/".$file_sync->getFilePath();
				}
			}
	
		}
	
		return false;
	}
    
	/** TODO - remove Deprecated **/
	private function DEPRECATED_getAdvancedEditorUiConf()
	{
		$c = new Criteria();
		$c->addAnd( uiConfPeer::DISPLAY_IN_SEARCH , mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK , Criteria::GREATER_EQUAL );
		$c->addAnd ( uiConfPeer::STATUS , uiConf::UI_CONF_STATUS_READY );
		$c->addAnd ( uiConfPeer::OBJ_TYPE , uiConf::UI_CONF_TYPE_ADVANCED_EDITOR );
		$c->addAnd ( uiConfPeer::TAGS, 'andromeda_vae_for_vmc', Criteria::LIKE);
		$c->addAscendingOrderByColumn(uiConfPeer::ID);

		$uiConf = uiConfPeer::doSelectOne($c);
		if ($uiConf)
			return $uiConf->getId();
		else
			return -1;
	}
	
	/** TODO - remove Deprecated **/
	private function DEPRECATED_getSimpleEditorUiConf()
	{
		$c = new Criteria();
		$c->addAnd( uiConfPeer::DISPLAY_IN_SEARCH , mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK , Criteria::GREATER_EQUAL );
		$c->addAnd ( uiConfPeer::STATUS , uiConf::UI_CONF_STATUS_READY );
		$c->addAnd ( uiConfPeer::OBJ_TYPE , uiConf::UI_CONF_TYPE_EDITOR );
		$c->addAnd ( uiConfPeer::TAGS, 'andromeda_vse_for_vmc', Criteria::LIKE);
		$c->addAscendingOrderByColumn(uiConfPeer::ID);

		$uiConf = uiConfPeer::doSelectOne($c);
		if ($uiConf)
			return $uiConf->getId();
		else
			return -1;
	}

	private function getCritria ( )
	{
		$c = new Criteria();
		
		// or belongs to the partner or a template  
		$criterion = $c->getNewCriterion( uiConfPeer::PARTNER_ID , $this->partner_id ) ; // or belongs to partner
		$criterion2 = $c->getNewCriterion( uiConfPeer::DISPLAY_IN_SEARCH , mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK , Criteria::GREATER_EQUAL );	// or belongs to vidiun_network == templates
		
		$criterion2partnerId = $c->getNewCriterion(uiConfPeer::PARTNER_ID, $this->templatePartnerId);
		$criterion2->addAnd($criterion2partnerId);  
		
		$criterion->addOr ( $criterion2 ) ;
		$c->addAnd ( $criterion );
		
		$c->addAnd ( uiConfPeer::OBJ_TYPE , uiConf::UI_CONF_TYPE_WIDGET );	//	only ones that are of type WIDGET
		$c->addAnd ( uiConfPeer::STATUS , uiConf::UI_CONF_STATUS_READY ); 	//	display only ones that are ready - not deleted or in draft mode
		
		
		$order_by = "(" . uiConfPeer::PARTNER_ID . "={$this->partner_id})";  // first take the templates  and then the rest
		$c->addAscendingOrderByColumn ( $order_by );//, Criteria::CUSTOM );

		return $c;
	}
	
	private function getUiconfList($tag = 'player')
	{
		$template_partner_id = (isset($this->templatePartnerId))? $this->templatePartnerId: self::SYSTEM_DEFAULT_PARTNER;
		$c = new Criteria();
		$crit_partner = $c->getNewCriterion(uiConfPeer::PARTNER_ID, $this->partner_id);
		 $crit_default = $c->getNewCriterion(uiConfPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK, Criteria::GREATER_EQUAL);
		
		$crit_default_partner_id = $c->getNewCriterion(uiConfPeer::PARTNER_ID, $template_partner_id);
		$crit_default_swf_url = $c->getNewCriterion(uiConfPeer::SWF_URL, '%/vdp3/%vdp3.swf', Criteria::LIKE);
		$crit_default->addAnd($crit_default_partner_id);
		$crit_default->addAnd($crit_default_swf_url);
		
		$crit_partner->addOr($crit_default);
		$c->add($crit_partner);
		$c->addAnd(uiConfPeer::OBJ_TYPE, array(uiConf::UI_CONF_TYPE_WIDGET, uiConf::UI_CONF_TYPE_VDP3), Criteria::IN);
		$c->addAnd ( uiConfPeer::STATUS , uiConf::UI_CONF_STATUS_READY );
		$c->addAnd ( uiConfPeer::TAGS, '%'.$tag.'%', Criteria::LIKE );
		$c->addAnd ( uiConfPeer::TAGS, '%jw'.$tag.'%', Criteria::NOT_LIKE );
		
		$c->addAnd ( uiConfPeer::ID, array(48501, 48502, 48504, 48505), Criteria::NOT_IN );
		
		$order_by = "(" . uiConfPeer::PARTNER_ID . "=".$this->partner_id.")";
		$c->addAscendingOrderByColumn ( $order_by );
		$c->addDescendingOrderByColumn(uiConfPeer::CREATED_AT);
		
		$confs = uiConfPeer::doSelect($c);
		return $confs;
	}	
}
