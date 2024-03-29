<?php
/**
 * 
 * @package Core
 * @subpackage externalWidgets
 */
class openGraphAction extends sfAction
{
	public function execute()
	{
		$widgetId = $this->getRequestParameter("wid");
		$widget = widgetPeer::retrieveByPK($widgetId);
		if(!$widget)
		{
			VidiunLog::err("Widget id [$widgetId] not found");
			die();
		}
		
		$entry = $widget->getentry();
		$entryId = $widget->getEntryId();
		if(!$entry)
		{
			VidiunLog::err("Entry id [$entryId] not found");
			die();
		}
		
		$uiConf = $widget->getuiConf();
		$uiConfId = $widget->getUiConfId();
		if(!$uiConf)
		{
			VidiunLog::err("UI Conf id [$uiConfId] not found");
			die();
		}

		$this->entry_name = $entry->getName();
		$this->entry_description = $entry->getDescription();
		$this->entry_thumbnail_url = $entry->getThumbnailUrl();
		$this->entry_thumbnail_secure_url = $entry->getThumbnailUrl(null, 'https');
		$this->entry_duration = $entry->getDuration();

		$flavor_tag = $this->getRequestParameter('flavor_tag', 'iphone');
		$flavor_assets = assetPeer::retrieveReadyFlavorsByEntryIdAndTag($entryId, $flavor_tag);
		$flavor_asset = reset($flavor_assets);
		$flavorId = null;
		if( $flavor_asset ) {
			$flavorId = $flavor_asset->getId();
		}	

		$embed_host = (vConf::hasParam('cdn_api_host')) ? vConf::get('cdn_api_host') : vConf::get('www_host');
		$embed_host_https = (vConf::hasParam('cdn_api_host_https')) ? vConf::get('cdn_api_host_https') : vConf::get('www_host');

		$https_enabled = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
		$protocol = ($https_enabled) ? 'https' : 'http';
		$port = ($_SERVER["SERVER_PORT"] != "80") ? ":".$_SERVER["SERVER_PORT"] : '';		

		$partnerId = $widget->getPartnerId();
		
		$this->widget = $widget;
		$this->entry = $entry; 
		$this->uiConf = $uiConf;

		// Build SWF Path
		$swfPath = "/index.php/vwidget/wid/" . $widgetId . "/uiconf_id/" . $uiConfId . "/entry_id/" . $entryId;
		// Set SWF URLs
		$this->swfUrl = 'http://' . $embed_host . $swfPath;
		$this->swfSecureUrl = 'https://' . $embed_host_https . $swfPath;		

		// set player url
		$this->playerUrl = 'https://' . $embed_host_https . '/p/'. $partnerId .'/sp/' . $partnerId . '00/embedIframeJs/uiconf_id/' . $uiConfId . '/partner_id/' . $partnerId . '?iframeembed=true&entry_id=' . $entryId . '&flashvars[streamerType]=auto';

		$host = ($https_enabled) ? $embed_host_https : $embed_host;
		$this->html5Url = $protocol . "://" . $host  . "/p/".$partnerId."/sp/".$partnerId."00/embedIframeJs/uiconf_id/".$uiConfId."/partner_id/". $partnerId;
		$this->pageURL = $protocol . '://' . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];

		$this->flavorUrl = null;
		if( isset($flavorId) ) {
			$this->flavorUrl = 'https://' . $embed_host_https . '/p/'. $partnerId .'/sp/' . $partnerId . '00/playManifest/entryId/' . $entryId . '/flavorId/' . $flavorId . '/format/url/protocol/' . $protocol . '/a.mp4';
		}
	}
}
