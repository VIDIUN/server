<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 * @relatedService EntryVendorTaskService
 */
class VidiunAlignmentVendorTaskData extends VidiunVendorTaskData
{
	/**
	 * The id of the text transcript object the vendor should use while runing the alignment task
	 * @var string
	 */
	public $textTranscriptAssetId;
	
	/**
	 * Optional - The id of the json transcript object the vendor should update once alignment task processing is done
	 * @insertonly
	 * @var string
	 */
	public $jsonTranscriptAssetId;
	
	/**
	 * Optional - The id of the caption asset object the vendor should update once alignment task processing is done
	 * @insertonly
	 * @var string
	 */
	public $captionAssetId;
	
	private static $map_between_objects = array
	(
		'textTranscriptAssetId',
		'jsonTranscriptAssetId',
		'captionAssetId',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vIdignmentVendorTaskData();
		}
		
		return parent::toObject($dbObject, $propsToSkip);
	}
	
	/* (non-PHPdoc)
 	 * @see VidiunObject::toInsertableObject()
 	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			$object_to_fill = new vIdignmentVendorTaskData();
		}
		
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("textTranscriptAssetId");
		$this->validateTranscriptAsset($this->textTranscriptAssetId, VidiunAttachmentType::TEXT);
		
		if($this->jsonTranscriptAssetId)
		{
			$this->validateTranscriptAsset($this->jsonTranscriptAssetId, VidiunAttachmentType::JSON);
		}
		
		if($this->captionAssetId)
		{
			$this->validateCaptionAsset($this->captionAssetId);
		}

		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		/* @var $sourceObject vIdignmentVendorTaskData */
		if(isset($this->textTranscriptAssetId) && $sourceObject->getTextTranscriptAssetId() != $this->textTranscriptAssetId)
		{
			$this->validateTranscriptAsset($this->textTranscriptAssetId, VidiunAttachmentType::TEXT);
		}
		
		/* @var $sourceObject vIdignmentVendorTaskData */
		if(isset($this->jsonTranscriptAssetId) && $sourceObject->getJsonTranscriptAssetId() != $this->jsonTranscriptAssetId)
		{
			$this->validateTranscriptAsset($this->jsonTranscriptAssetId, VidiunAttachmentType::JSON);
		}
		
		/* @var $sourceObject vIdignmentVendorTaskData */
		if(isset($this->captionAssetId) && $sourceObject->getCaptionAssetId() != $this->captionAssetId)
		{
			$this->validateCaptionAsset($this->captionAssetId);
		}

		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}

	protected function validateTranscriptAsset($transcriptAssetId, $expectedType)
	{
		$transcriptAssetDb = assetPeer::retrieveById($transcriptAssetId);
		if (!$transcriptAssetDb || !($transcriptAssetDb instanceof TranscriptAsset))
		{
			throw new VidiunAPIException(VidiunAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $transcriptAssetId);
		}
		
		/* @var $transcriptAssetDb TranscriptAsset */
		if($transcriptAssetDb->getContainerFormat() != $expectedType)
		{
			throw new VidiunAPIException(VidiunAttachmentErrors::ATTACHMENT_ASSET_FORMAT_MISMATCH, $transcriptAssetId, $expectedType);
		}
	}
	
	protected function validateCaptionAsset($captionAssetId)
	{
		$captionAssetDb = assetPeer::retrieveById($captionAssetId);
		if (!$captionAssetDb || !($captionAssetDb instanceof CaptionAsset))
		{
			throw new VidiunAPIException(VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND, $captionAssetId);
		}
	}
}
