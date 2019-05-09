<?php
/**
 * Handles thumb cue point ingestion from XML bulk upload
 * @package plugins.thumbCuePoint
 * @subpackage batch
 */
class ThumbCuePointBulkUploadXmlHandler extends CuePointBulkUploadXmlHandler
{
	/**
	 * @var ThumbCuePointBulkUploadXmlHandler
	 */
	protected static $instance;
	
	/**
	 * @return ThumbCuePointBulkUploadXmlHandler
	 */
	public static function get()
	{
		if(!self::$instance)
			self::$instance = new ThumbCuePointBulkUploadXmlHandler();
			
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see CuePointBulkUploadXmlHandler::getNewInstance()
	 */
	protected function getNewInstance()
	{
		return new VidiunThumbCuePoint();
	}
	
	/* (non-PHPdoc)
	 * @see CuePointBulkUploadXmlHandler::parseCuePoint()
	 */
	protected function parseCuePoint(SimpleXMLElement $scene)
	{
		if($scene->getName() != 'scene-thumb-cue-point')
			return null;
			
		$cuePoint = parent::parseCuePoint($scene);
		if(!($cuePoint instanceof VidiunThumbCuePoint))
			return null;
			
		//If timedThumbAssetId is present in the XML assume an existing one is beeing updated (Action = Update)
		if(isset($scene->slide) && isset($scene->slide->timedThumbAssetId))
			$cuePoint->assetId  = $scene->slide->timedThumbAssetId;
			
		$cuePoint->title = $scene->title;
		$cuePoint->description = $scene->description;
		
		if(isset($scene->subType))
			$cuePoint->subType = $scene->subType;
		else 
			$cuePoint->subType = VidiunThumbCuePointSubType::SLIDE;
		
		return $cuePoint;
	}
	
	protected function handleResults(array $results, array $items)
	{	
		//Added to support cases where the resource is entry resource
		$conversionProfileId = null;
		try {
			VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
			$entry = VBatchBase::$vClient->baseEntry->get($this->entryId);
			VBatchBase::unimpersonate();
			if($entry && $entry->conversionProfileId)
				$conversionProfileId = $entry->conversionProfileId;
		}
		catch (Exception $ex)
		{
			VBatchBase::unimpersonate();
			VidiunLog::info("Entry ID [" . $this->entryId . "] not found, continuing with no conversion profile");
		}
		
		foreach($results as $index => $cuePoint)
		{	
			if($cuePoint instanceof VidiunThumbCuePoint)
			{
				if(!isset($items[$index]->slide) || empty($items[$index]->slide))
					continue;
				
				$timedThumbResource = $this->xmlBulkUploadEngine->getResource($items[$index]->slide, $conversionProfileId);
				$thumbAsset = new VidiunTimedThumbAsset();
				$thumbAsset->cuePointId = $cuePoint->id;

				VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
				VBatchBase::$vClient->startMultiRequest();
				VBatchBase::$vClient->thumbAsset->add($cuePoint->entryId, $thumbAsset);
				VBatchBase::$vClient->thumbAsset->setContent(VBatchBase::$vClient->getMultiRequestResult()->id, $timedThumbResource);
				VBatchBase::$vClient->doMultiRequest();
				VBatchBase::unimpersonate();
			}
				
		}
		
		return parent::handleResults($results, $items);
	}

}