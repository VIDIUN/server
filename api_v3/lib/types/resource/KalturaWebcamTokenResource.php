<?php
/**
 * Used to ingest media that streamed to the system and represented by token that returned from media server such as FMS or red5.
 *
 * @package api
 * @subpackage objects
 */
class VidiunWebcamTokenResource extends VidiunDataCenterContentResource
{
	/**
	 * Token that returned from media server such as FMS or red5.
	 * @var string
	 */
	public $token;
	
	/* (non-PHPdoc)
	 * @see VidiunDataCenterContentResource::getDc()
	 */
	public function getDc()
	{
	    $content = myContentStorage::getFSContentRootPath();
	    $entryFullPaths = array(
	    	"{$content}/content/webcam/{$this->token}.flv",
	    	"{$content}/content/webcam/{$this->token}.f4v",
	    	"{$content}/content/webcam/{$this->token}.f4v.mp4",
	    );
	    
	    foreach($entryFullPaths as $entryFullPath)
			if(file_exists($entryFullPath))
				return vDataCenterMgr::getCurrentDcId();
			
		return (1 - vDataCenterMgr::getCurrentDcId()); // other dc
	}
	
	/* (non-PHPdoc)
	 * @see VidiunDataCenterContentResource::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('token');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunResource::entryHandled()
	 */
	public function entryHandled(entry $dbEntry)
	{
		parent::entryHandled($dbEntry);
		
		$originalFlavorAsset = assetPeer::retrieveOriginalByEntryId($dbEntry->getId());
		$syncKey = $originalFlavorAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$sourceFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey);
		
		// call mediaInfo for file
		$dbMediaInfo = new mediaInfo();
		try
		{
			$mediaInfoParser = new VMediaInfoMediaParser($sourceFilePath, vConf::get('bin_path_mediainfo'));
			$mediaInfo = $mediaInfoParser->getMediaInfo();
			$dbMediaInfo = $mediaInfo->toInsertableObject($dbMediaInfo);
			$dbMediaInfo->setFlavorAssetId($originalFlavorAsset->getId());
			$dbMediaInfo->save();
		}
		catch(Exception $e)
		{
			VidiunLog::err("Getting media info: " . $e->getMessage());
			$dbMediaInfo = null;
		}
		
		// fix flavor asset according to mediainfo
		if($dbMediaInfo)
		{
			VDLWrap::ConvertMediainfoCdl2FlavorAsset($dbMediaInfo, $originalFlavorAsset);
			$flavorTags = VDLWrap::CDLMediaInfo2Tags($dbMediaInfo, array(flavorParams::TAG_WEB));
			$originalFlavorAsset->setTags(implode(',', array_unique($flavorTags)));
		}
		$originalFlavorAsset->setStatusLocalReady();
		$originalFlavorAsset->save();
		
		$dbEntry->setStatus(entryStatus::READY);
		$dbEntry->save();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		$this->validateForUsage($object_to_fill, $props_to_skip);
		
		if(!$object_to_fill)
			$object_to_fill = new vLocalFileResource();
			
	    $content = myContentStorage::getFSContentRootPath();
	    $entryFullPaths = array(
	    	'flv' => "{$content}/content/webcam/{$this->token}.flv",
	    	'f4v' => "{$content}/content/webcam/{$this->token}.f4v",
	    	'mp4' => "{$content}/content/webcam/{$this->token}.f4v.mp4",
	    );
	    
	    foreach($entryFullPaths as $type => $entryFullPath)
	    {
			if(file_exists($entryFullPath))
			{
				if($type == 'flv')
				{
					$entryFixedFullPath = $entryFullPath . '.fixed.flv';
			 		VidiunLog::info("Fix webcam full path from [$entryFullPath] to [$entryFixedFullPath]");
					myFlvStaticHandler::fixRed5WebcamFlv($entryFullPath, $entryFixedFullPath);
							
					$entryNewFullPath = $entryFullPath . '.clipped.flv';
			 		VidiunLog::info("Clip webcam full path from [$entryFixedFullPath] to [$entryNewFullPath]");
					myFlvStaticHandler::clipToNewFile($entryFixedFullPath, $entryNewFullPath, 0, 0);
					$entryFullPath = $entryNewFullPath ;
							
					if(!file_exists($entryFullPath))
						throw new VidiunAPIException(VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND);
				}
							
				$object_to_fill->setSourceType(VidiunSourceType::WEBCAM);
				$object_to_fill->setLocalFilePath($entryFullPath);
				return $object_to_fill;
			}
	    }
		
		throw new VidiunAPIException(VidiunErrors::RECORDED_WEBCAM_FILE_NOT_FOUND);
	}
}