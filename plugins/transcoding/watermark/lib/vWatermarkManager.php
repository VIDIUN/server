<?php

/**
 * @package plugins.watermark
 * @subpackage lib
 */
class vWatermarkManager
{
	/**
	 *
	 * @param unknown_type $watermarkData
	 * @param unknown_type $watermarkToMerge
	 */
	public static function adjustWatermarkSettings($watermarkData, $watermarkToMerge)
	{
		VidiunLog::log("Merge WM (".serialize($watermarkToMerge).") into (".serialize($watermarkData).")");
		if(is_array($watermarkData))
			$watermarkDataArr = $watermarkData;
		else
			$watermarkDataArr = array($watermarkData);
		
		if(is_array($watermarkToMerge))
			$watermarkToMergeArr = $watermarkToMerge;
		else
			$watermarkToMergeArr = array($watermarkToMerge);
		
		foreach($watermarkToMergeArr as $wmI=>$watermarkToMerge)
		{
			VidiunLog::log("Merging WM:$wmI");
			if(!array_key_exists($wmI, $watermarkDataArr))
			{
				$watermarkDataArr[$wmI] = $watermarkToMerge;
				VidiunLog::log("Added object ($wmI)-".serialize($watermarkToMerge));
				continue;
			}
			
			foreach($watermarkToMerge as $fieldName=>$fieldValue)
			{
				$watermarkDataArr[$wmI]->$fieldName = $fieldValue;
				VidiunLog::log("set($fieldName):".$fieldValue);
				switch($fieldName){
					case "imageEntry":
						VidiunLog::log("unset(url):".$watermarkDataArr[$wmI]->url);
						unset($watermarkDataArr[$wmI]->url);
						break;
					case  "url":
						VidiunLog::log("unset(imageEntry):".$watermarkDataArr[$wmI]->imageEntry);
						unset($watermarkDataArr[$wmI]->imageEntry);
						break;
				}
			}
		}
		
		VidiunLog::log("Merged WM (".serialize($watermarkDataArr).")");
		return $watermarkDataArr;
	}
	
	public static function getWatermarkMetadata($entry)
	{
		$entryId = $entry->getId();
		$partnerId = $entry->getPartnerId();
		$profile = MetadataProfilePeer::retrieveBySystemName(WatermarkPlugin::TRANSCODING_METADATA_PROF_SYSNAME,$partnerId);
		if(!isset($profile))
		{
			VidiunLog::log("Missing Transcoding Metadata Profile (sysName:TRANSCODINGPARAMS, partner:$partnerId)s");
			return null;
		}
		
		$profileId = $profile->getId();
		$metadata = MetadataPeer::retrieveByObject($profileId, MetadataObjectType::ENTRY, $entryId);
		if(!isset($metadata))
			VidiunLog::log("Missing Metadata for entry($entryId), metadata profile (id:$profileId)!");
		
		return $metadata;
	}
	
	/**
	 * $entry
	 */
	public static function getWatermarkMetadataXml($entry)
	{
		$entryId = $entry->getId();
		$metadata = self::getWatermarkMetadata($entry);
		if(!$metadata)
			return null;
		
		VidiunLog::log("Entry ($entryId) has following metadata fields:".print_r($metadata,1));
		
		// Retrieve the associated XML file
		$key = $metadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		if(!isset($key))
		{
			VidiunLog::log("Missing file sync key for entry($entryId) metadata object!");
			return null;
		}
		$xmlData = vFileSyncUtils::file_get_contents($key, true, false);
		if(!isset($xmlData)){
			VidiunLog::log("Missing valid file sync for entry($entryId) metadata object!");
			return null;
		}
		return $xmlData;
	}
	
	public static function copyWatermarkData(Metadata $watermarkMetadata, entry $fromEntry, entry $toEntry)
	{
		VidiunLog::debug("copyWatermarkData from [{$fromEntry->getId()}] to [{$toEntry->getId()}]");
		$copyWatermarkMetadata = $watermarkMetadata->copy();
		$copyWatermarkMetadata->setObjectId($toEntry->getId());
		$copyWatermarkMetadata->save();
		
		$srcSyncKey = $watermarkMetadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		$destinationSyncKey = $copyWatermarkMetadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		
		vFileSyncUtils::softCopy($srcSyncKey, $destinationSyncKey);
	}
}