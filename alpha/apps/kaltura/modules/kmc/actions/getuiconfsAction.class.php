<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class getuiconfsAction extends vidiunAction
{
	public function execute ( ) 
	{
		header('Access-Control-Allow-Origin:*');

		$this->partner_id = $this->getP ( "partner_id" );
		$this->vs = $this->getP ( "vs" );
		$type = $this->getP("type");
		
		$this->partner = PartnerPeer::retrieveByPK($this->partner_id);
		if (!$this->partner)
			VExternalErrors::dieError( VExternalErrors::PARTNER_NOT_FOUND );
					
		if (!$this->partner->validateApiAccessControl())
			VExternalErrors::dieError( VExternalErrors::SERVICE_ACCESS_CONTROL_RESTRICTED );
			
		$this->templatePartnerId = $this->partner ? $this->partner->getTemplatePartnerId() : 0;
		$this->isVDP3 = ($this->partner->getVmcVersion() != '1')? true: false;

		// FIXME: validate the vs!
		
		
		$partnerUiconfs = vmcUtils::getPartnersUiconfs($this->partner_id, $type);
		$partner_uiconfs_array = array();
		foreach($partnerUiconfs as $uiconf)
		{
			$uiconf_array = array();
			$uiconf_array["id"] = $uiconf->getId();
			$uiconf_array["name"] = $uiconf->getName();
			$uiconf_array["width"] = $uiconf->getWidth();
			$uiconf_array["height"] = $uiconf->getHeight();
			//$uiconf_array["swfUrlVersion"] = $uiconf->getSwfUrlVersion();
			$uiconf_array["swf_version"] = "v" . $uiconf->getSwfUrlVersion();
			$uiconf_array["html5Url"] = $uiconf->getHtml5Url();
            $uiconf_array["updatedAt"] = $uiconf->getUpdatedAt(null);

			$partner_uiconfs_array[] = $uiconf_array;
		}
		
		// default uiconf array
		$this->vmc_swf_version = vConf::get('vmc_version');
		$vmcGeneralUiConf = array();
		$vmcGeneralTemplateUiConf = array();
		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_HIDE_TEMPLATE_PARTNER_UICONFS, $this->partner->getId()))
		{
			$vmcGeneralUiConf = vmcUtils::getAllVMCUiconfs('vmc',   $this->vmc_swf_version, $this->templatePartnerId);
			$vmcGeneralTemplateUiConf = vmcUtils::getAllVMCUiconfs('vmc',   $this->vmc_swf_version, $this->templatePartnerId);
		}
			
		if($type == 'player')
		{
			$content_uiconfs_previewembed = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_previewembed", true, $vmcGeneralUiConf);
		}
		else
		{
			$content_uiconfs_previewembed = vmcUtils::find_confs_by_usage_tag($vmcGeneralTemplateUiConf, "vmc_previewembed_list", true, $vmcGeneralUiConf);
		}
		
		$default_uiconfs_array = array();
		foreach($content_uiconfs_previewembed as $uiconf)
		{
			$uiconf_array = array();
			$uiconf_array["id"] = $uiconf->getId();
			$uiconf_array["name"] = $uiconf->getName();
			$uiconf_array["width"] = $uiconf->getWidth();
			$uiconf_array["height"] = $uiconf->getHeight();
			//$uiconf_array["swfUrlVersion"] = $uiconf->getSwfUrlVersion();
			$uiconf_array["swf_version"] = "v" . $uiconf->getSwfUrlVersion();
			$uiconf_array["html5Url"] = $uiconf->getHtml5Url();
			$uiconf_array["updatedAt"] = $uiconf->getUpdatedAt(null);

			$default_uiconfs_array[] = $uiconf_array;
		}
		
		$vdp508_uiconfs = array();
		if($type == 'player' && $this->partner->getEnable508Players())
		{
			$vdp508_uiconfs = vmcUtils::getPlayerUiconfsByTag('vdp508');
		}

		// Add HTML5 v2.0.0 Preview Player
		$v2_preview_players = array();
		if( $type == 'player'&& PermissionPeer::isValidForPartner(PermissionName::FEATURE_HTML5_V2_PLAYER_PREVIEW, $this->partner_id)){
			$v2_preview_players = vmcUtils::getPlayerUiconfsByTag('html5_v2_preview');
		}
		
		$merged_list = array();
		if(count($default_uiconfs_array))
			foreach($default_uiconfs_array as $uiconf)
				$merged_list[] = $uiconf;
		if(count($vdp508_uiconfs))
			foreach($vdp508_uiconfs as $uiconf)
				$merged_list[] = $uiconf;
		if(count($v2_preview_players))
			foreach($v2_preview_players as $uiconf)
				$merged_list[] = $uiconf;			
		if(count($partner_uiconfs_array))
			foreach($partner_uiconfs_array as $uiconf)
				$merged_list[] = $uiconf;

		return $this->renderText(json_encode($merged_list));
	}
}
