<?php
/**
 * Adjust asset-params with watermarks according to custom metadata
 *
 * @package plugins.watermark
 */
class WatermarkPlugin extends VidiunPlugin implements IVidiunPending, IVidiunAssetParamsAdjuster, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'watermark';
	
	const METADATA_PLUGIN_NAME = 'metadata';
	const METADATA_PLUGIN_VERSION_MAJOR = 1;
	const METADATA_PLUGIN_VERSION_MINOR = 0;
	const METADATA_PLUGIN_VERSION_BUILD = 0;

	const TRANSCODING_METADATA_PROF_SYSNAME = 'TRANSCODINGPARAMS';
		
	const TRANSCODING_METADATA_WATERMMARK_SETTINGS = 'WatermarkSettings';
	const TRANSCODING_METADATA_WATERMMARK_IMAGE_ENTRY = 'WatermarkImageEntry';
	const TRANSCODING_METADATA_WATERMMARK_IMAGE_URL = 'WatermarkImageURL';
	
	const WATERMARK_FLOW_MANAGER_CLASS = 'vWatermarkFlowManager';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$metadataVersion = new VidiunVersion(self::METADATA_PLUGIN_VERSION_MAJOR, self::METADATA_PLUGIN_VERSION_MINOR, self::METADATA_PLUGIN_VERSION_BUILD);
		$metadataDependency = new VidiunDependency(self::METADATA_PLUGIN_NAME, $metadataVersion);
		
		return array($metadataDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
 	 */
	public static function getEventConsumers()
	{
		return array(
			self::WATERMARK_FLOW_MANAGER_CLASS,
		);
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunAssetParamsAdjuster::adjustAssetParams()
	 */
	public function adjustAssetParams($entryId, array &$flavors)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!isset($entry))
		{
			VidiunLog::warning("Bad entry id ($entryId).");
			return;
		}
		
		$xmlStr = vWatermarkManager::getWatermarkMetadataXml($entry);
		if(!isset($xmlStr))
		{
			VidiunLog::log("Entry($entryId) metadata object misses valid file sync! Nothing to adjust");
			return;
		}
		
		VidiunLog::log("Adjusting: entry($entryId),metadata profile(".self::TRANSCODING_METADATA_PROF_SYSNAME."),xml==>$xmlStr");

		// Retrieve the custom metadata fields from the asocieted XML
		
		
		/*
		 * Acquire the optional 'full' WM settings (TRANSCODING_METADATA_WATERMMARK_SETTINGS) 
		 * adjust it to custom meta imageEntry/imageUrl values,
		 * if those provided.
		 */
		$watermarkSettings = array();
		$xml = new SimpleXMLElement($xmlStr);
		$fldName = self::TRANSCODING_METADATA_WATERMMARK_SETTINGS;

		if(isset($xml->$fldName)) 
		{
			$watermarkSettingsStr =(string)$xml->$fldName;
			VidiunLog::log("Found custom metadata - $fldName($watermarkSettingsStr)");
			if(isset($watermarkSettingsStr)) 
			{
				$watermarkSettings = json_decode($watermarkSettingsStr);
				if(!is_array($watermarkSettings)) 
				{
					$watermarkSettings = array($watermarkSettings);
				}
				VidiunLog::log("WM($fldName) object:".serialize($watermarkSettings));
			}
		}
		else
			VidiunLog::log("No custom metadata - $fldName");

		/*
		 * Acquire the optional partial WM settings ('imageEntry'/'url') 
		 * Prefer the 'imageEntry' in case when both 'imageEntr' and 'url' are previded ('url' ignored).
		 */
		$wmTmp = null;
		$fldName = self::TRANSCODING_METADATA_WATERMMARK_IMAGE_ENTRY;
		if(isset($xml->$fldName)) 
		{
			$wmTmp->imageEntry =(string)$xml->$fldName;
			VidiunLog::log("Found custom metadata - $fldName($wmTmp->imageEntry)");
		}
		else 
		{
			VidiunLog::log("No custom metadata - $fldName");
			$fldName = self::TRANSCODING_METADATA_WATERMMARK_IMAGE_URL;
			if(isset($xml->$fldName)) 
			{
				$fldVal = (string)$xml->$fldName;
				$wmTmp->url =(string)$xml->$fldName;
				VidiunLog::log("Found custom metadata - $fldName($wmTmp->url)");
			}
			else 
				VidiunLog::log("No custom metadata - $fldName");
		}
		
		/*
		 * Merge the imageEntry/imageUrl values into previously aquired 'full' WM settings (if provided).
		 */
		if(isset($wmTmp))
			$watermarkSettings = vWatermarkManager::adjustWatermarkSettings($watermarkSettings, $wmTmp);
		VidiunLog::log("Custom meta data WM settings:".serialize($watermarkSettings));

		/*
		 * Check for valuable WM custom data.
		 * If none - leave
		 */
		{
			$fldCnt = 0;
			foreach($watermarkSettings as $wmI=>$wmTmp)
			{
				if(isset($wmTmp))
				{
					$fldCnt+= count((array)$wmTmp);
				}
			}
			if($fldCnt==0)
			{
				VidiunLog::log("No WM custom data to merge");
				return;
			}
		}
		
		/*
		 * Loop through the flavor params to update the WM settings,
		 * if it is required.
		 */
		foreach($flavors as $k=>$flavor) 
		{
			VidiunLog::log("Processing flavor id:".$flavor->getId());
			$wmDataFixed = null;
			$wmPredefined = null;
			$wmPredefinedStr = $flavor->getWatermarkData();
			if(!(isset($wmPredefinedStr) && ($wmPredefined=json_decode($wmPredefinedStr))!=null))
			{
				VidiunLog::log("No WM data for flavor:".$flavor->getId());
				continue;
			}
			VidiunLog::log("wmPredefined : count(".count($wmPredefined).")-".serialize($wmPredefined));

			$wmDataFixed = vWatermarkManager::adjustWatermarkSettings($wmPredefined, $watermarkSettings);

			/*
			 * The 'full' WM settings in the custom metadata overides any exitings WM settings 
			 */
			$wmJsonStr = json_encode($wmDataFixed);
			$flavor->setWatermarkData($wmJsonStr);
			$flavors[$k]= $flavor;
			VidiunLog::log("Update flavor (".$flavor->getId().") WM to: $wmJsonStr");
		}
	}
}
