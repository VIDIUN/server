<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunLiveEntry extends VidiunMediaEntry
{
	const MIN_ALLOWED_SEGMENT_DURATION_MILLISECONDS = 1000;
	const MAX_ALLOWED_SEGMENT_DURATION_MILLISECONDS = 20000;

	/**
	 * The message to be presented when the stream is offline
	 * 
	 * @var string
	 */
	public $offlineMessage;
	
	/**
	 * Recording Status Enabled/Disabled
	 * @var VidiunRecordStatus
	 */
	public $recordStatus;
	
	/**
	 * DVR Status Enabled/Disabled
	 * @var VidiunDVRStatus
	 */
	public $dvrStatus;
	
	/**
	 * Window of time which the DVR allows for backwards scrubbing (in minutes)
	 * @var int
	 */
	public $dvrWindow;
	
	/**
	 * Elapsed recording time (in msec) up to the point where the live stream was last stopped (unpublished).
	 * @var int
	 */
	public $lastElapsedRecordingTime;

	/**
	 * Array of key value protocol->live stream url objects
	 * @var VidiunLiveStreamConfigurationArray
	 */
	public $liveStreamConfigurations;
	
	/**
	 * Recorded entry id
	 * 
	 * @var string
	 */
	public $recordedEntryId;
	

	/**
	 * Flag denoting whether entry should be published by the media server
	 * 
	 * @var VidiunLivePublishStatus
	 * @requiresPermission all
	 */
	public $pushPublishEnabled;
	
	/**
	 * Array of publish configurations
	 * 
	 * @var VidiunLiveStreamPushPublishConfigurationArray
	 * @requiresPermission all
	 */
	public $publishConfigurations;
	
	/**
	 * The first time in which the entry was broadcast
	 * @var int
	 * @readonly
	 * @filter order
	 */
	public $firstBroadcast;
	
	/**
	 * The Last time in which the entry was broadcast
	 * @var int
	 * @readonly
	 * @filter order
	 */
	public $lastBroadcast;
	
	/**
	 * The time (unix timestamp in milliseconds) in which the entry broadcast started or 0 when the entry is off the air
	 * @var float
	 */
	public $currentBroadcastStartTime;

	/**
	 * @var VidiunLiveEntryRecordingOptions
	 */
	public $recordingOptions;

	/**
	 * the status of the entry of type EntryServerNodeStatus
	 * @var VidiunEntryServerNodeStatus
	 * @readonly
	 * @deprecated use VidiunLiveStreamService.isLive instead
	 */
	public $liveStatus;

	/**
	 * The chunk duration value in milliseconds
	 * @var int
	 */
	public $segmentDuration;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $explicitLive;

	/**
	 * @var VidiunViewMode
	 */
	public $viewMode;

	/**
	 * @var VidiunRecordingStatus
	 */
	public $recordingStatus;

	/**
	 * The time the last broadcast finished.
	 * @var int
	 * @readonly
	 */
	public $lastBroadcastEndTime;

	private static $map_between_objects = array
	(
		"offlineMessage",
	    "recordStatus",
	    "dvrStatus",
	    "dvrWindow",
		"lastElapsedRecordingTime",
		"liveStreamConfigurations",
		"recordedEntryId",
		"pushPublishEnabled",
		"firstBroadcast",
		"lastBroadcast",
		"publishConfigurations",
		"currentBroadcastStartTime",
		"recordingOptions",
		"liveStatus",
		"segmentDuration",
		"explicitLive",
		"viewMode",
		"recordingStatus",
		"lastBroadcastEndTime",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunMediaEntry::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toInsertableObject($sourceObject = null, $propsToSkip = array())
	{
		$isRecordPermissionValidForPartner = PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM_RECORD, vCurrentContext::getCurrentPartnerId()) ||
				PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM_VIDIUN_RECORDING, vCurrentContext::getCurrentPartnerId());
		
		if(isset($this->recordStatus) && $this->recordStatus != VidiunRecordStatus::DISABLED && !$isRecordPermissionValidForPartner)
			throw new VidiunAPIException(VidiunErrors::RECORDING_DISABLED);
		
		if(is_null($this->recordStatus))
		{
			$this->recordStatus = VidiunRecordStatus::DISABLED;
			if($isRecordPermissionValidForPartner)
			{
				$this->recordStatus = VidiunRecordStatus::APPENDED;
			}
		}

		if ((is_null($this->recordingOptions) || is_null($this->recordingOptions->shouldCopyEntitlement)) && PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM_COPY_ENTITELMENTS, vCurrentContext::getCurrentPartnerId()))
		{
			if (is_null($this->recordingOptions))
			{
				$this->recordingOptions = new VidiunLiveEntryRecordingOptions();
			}
			$this->recordingOptions->shouldCopyEntitlement = true;
		}

		return parent::toInsertableObject($sourceObject, $propsToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunMediaEntry::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if(!($dbObject instanceof LiveEntry))
			return;
			
		parent::doFromObject($dbObject, $responseProfile);

		if($this->shouldGet('recordingOptions', $responseProfile) && !is_null($dbObject->getRecordingOptions()))
		{
			$this->recordingOptions = new VidiunLiveEntryRecordingOptions();
			$this->recordingOptions->fromObject($dbObject->getRecordingOptions());
		}

		if ($dbObject->isPlayable())
		{
			vApiCache::setExpiry( vApiCache::REDIRECT_ENTRY_CACHE_EXPIRY );
		}
	}

	public function validateConversionProfile(entry $sourceObject = null)
	{
		if(!is_null($this->conversionProfileId) && $this->conversionProfileId != conversionProfile2::CONVERSION_PROFILE_NONE)
		{
			$conversionProfile = conversionProfile2Peer::retrieveByPK($this->conversionProfileId);
			if(!$conversionProfile || $conversionProfile->getType() != ConversionProfileType::LIVE_STREAM)
				throw new VidiunAPIException(VidiunErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $this->conversionProfileId);
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validateSegmentDurationValue(null, "segmentDuration");

		return parent::validateForInsert($propertiesToSkip);

	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate($source_object)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$updateValidateAttributes = array(
				"dvrStatus" => array("validatePropertyChanged"), 
				"dvrWindow" => array("validatePropertyChanged"), 
				"recordingOptions" => array("validateRecordingOptionsChanged"),
				"recordStatus" => array("validatePropertyChanged","validateRecordedEntryId"), 
				"conversionProfileId" => array("validatePropertyChanged","validateRecordedEntryId"),
				"segmentDuration" => array("validatePropertyChanged", "validateSegmentDurationValue"),
				"explicitLive" => array("validatePropertyChanged"),
		);
		
		foreach ($updateValidateAttributes as $attr => $validateFucntions)
		{
			if(isset($this->$attr))
			{
				foreach ($validateFucntions as $function)
				{
					$this->$function($sourceObject, $attr);
				}
			}
		}
		
		parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	protected function validatePropertyChanged($sourceObject, $attr)
	{
		if($this->hasPropertyChanged($sourceObject, $attr) && $sourceObject->getLiveStatus() !== VidiunEntryServerNodeStatus::STOPPED )
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_FIELDS_WHILE_ENTRY_BROADCASTING, $attr);
		}
	}
	
	protected function validateRecordedEntryId($sourceObject, $attr)
	{
		if($this->hasPropertyChanged($sourceObject, $attr))
			$this->validateRecordingDone($sourceObject, $attr);
	}
	
	private function validateRecordingDone($sourceObject, $attr)
	{
		/* @var $sourceObject LiveEntry */
		$recordedEntry = $sourceObject->getRecordedEntryId() ? entryPeer::retrieveByPK($sourceObject->getRecordedEntryId()) : null;
		if($recordedEntry)
		{
			$validUpdateStatuses = array(VidiunEntryStatus::READY, VidiunEntryStatus::ERROR_CONVERTING, VidiunEntryStatus::ERROR_IMPORTING);
			if( !in_array($recordedEntry->getStatus(), $validUpdateStatuses) )
				throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_FIELDS_RECORDED_ENTRY_STILL_NOT_READY, $attr);
			
			$noneReadyAssets = assetPeer::retrieveByEntryId($recordedEntry->getId(),
					array(VidiunAssetType::FLAVOR),
					array(VidiunFlavorAssetStatus::CONVERTING, VidiunFlavorAssetStatus::QUEUED, VidiunFlavorAssetStatus::WAIT_FOR_CONVERT, VidiunFlavorAssetStatus::VALIDATING));
			
			if(count($noneReadyAssets))
				throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_FIELDS_RECORDED_ENTRY_STILL_NOT_READY, $attr);
		}
	}
	
	protected function validateRecordingOptionsChanged($sourceObject, $attr)
	{
		if(!isset($this->recordingOptions))
			return;
		
		if(!isset($this->recordingOptions->shouldCopyEntitlement))
			return;
		
		/* @var $sourceObject LiveEntry */
		$hasObjectChanged = false;
		if( !$sourceObject->getRecordingOptions() || ($sourceObject->getRecordingOptions() && $sourceObject->getRecordingOptions()->getShouldCopyEntitlement() !== $this->recordingOptions->shouldCopyEntitlement) )
			$hasObjectChanged = true;
		
		if($hasObjectChanged)
		{
			if( $sourceObject->getLiveStatus() !== VidiunEntryServerNodeStatus::STOPPED)
				throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_FIELDS_WHILE_ENTRY_BROADCASTING, "recordingOptions");
			
			$this->validateRecordingDone($sourceObject, "recordingOptions");
		}
	}

	private function validateSegmentDurationValue($sourceObject, $attr)
	{

		if (!$this->isNull($attr) && $this->hasPropertyChanged($sourceObject, $attr)) 
		{
			if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_DYNAMIC_SEGMENT_DURATION, vCurrentContext::getCurrentPartnerId())) 
			{
				throw new VidiunAPIException(VidiunErrors::DYNAMIC_SEGMENT_DURATION_DISABLED, $this->getFormattedPropertyNameWithClassName($attr));
			}

			$this->validatePropertyNumeric($attr);
			$this->validatePropertyMinMaxValue($attr, self::MIN_ALLOWED_SEGMENT_DURATION_MILLISECONDS, self::MAX_ALLOWED_SEGMENT_DURATION_MILLISECONDS);
		}
	}
	
	private function hasPropertyChanged($sourceObject, $attr)
	{
		$resolvedAttrName = $this->getObjectPropertyName($attr);
		if(!$resolvedAttrName)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_IS_NOT_DEFINED, $attr, get_class($this));
		
		/* @var $sourceObject LiveEntry */
		$getter = "get" . ucfirst($resolvedAttrName);
		if(!$sourceObject || $sourceObject->$getter() !== $this->$attr)
			return true;
		
		return false;
	}

}
