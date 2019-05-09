<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
require_once ( __DIR__ . "/defVeditorservicesAction.class.php");

/**
 * @package    Core
 * @subpackage vEditorServices
 */
class getEntryInfoAction extends defVeditorservicesAction
{
	public function execute()
	{
		$this->vuser = null;
		return parent::execute();
	}
	
	// here the $vshow will be null (thanks to fetchVshow=false) and entry will 
	public  function executeImpl ( vshow $vshow, entry &$entry )
	{
		$genericWidget = "";
		$myspaceWidget = "";
		
		$vshow_id = $vshow->getId();
		$entry_id = $entry->getId();
		
		if (!$vshow->getPartnerId() && !$this->forceViewPermissions ( $vshow, $vshow_id , false , false ))
			die;
		
		$this->vshow_category  = $vshow->getTypeText();
		$this->vshow_description = $vshow->getDescription();
		$this->vshow_name = $vshow->getName();
		$this->vshow_tags = $vshow->getTags();
		
		$vdata = @$_REQUEST["vdata"];
		if ($vdata == "null")
			$vdata = "";
			
		$this->widget_type = @$_REQUEST["widget_type"];
		
		list($genericWidget, $myspaceWidget) = myVshowUtils::getEmbedPlayerUrl($vshow_id, $entry_id, false, $vdata); 
		
		if ($entry_id == 1002)
			$this->share_url = requestUtils::getHost() .  "/index.php/corp/vidiunPromo";
		else if ($vdata)
			$this->share_url = myVshowUtils::getWidgetCmdUrl($vdata, "share");
		else
			$this->share_url = myVshowUtils::getUrl( $vshow_id )."&entry_id=$entry_id";
		
		//list($status, $vmediaType, $vmediaData) = myContentRender::createPlayerMedia($entry); // myContentRender class removed, old code
		$status = $entry->getStatus();
		$vmediaType = $entry->getMediaType();
		$vmediaData = "";
		
		$this->message = ($vmediaType == entry::ENTRY_MEDIA_TYPE_TEXT) ? $vmediaData : "";
		
		$this->generic_embed_code = $genericWidget;
		$this->myspace_embed_code = $myspaceWidget;
		$this->thumbnail = $entry ? $entry->getBigThumbnailPath(true) : "";
		$this->vuser = $entry->getVuser();
		$this->entry = $entry;		
	}
}

?>