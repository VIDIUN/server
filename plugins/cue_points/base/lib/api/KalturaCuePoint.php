<?php
/**
 * @package plugins.cuePoint
 * @subpackage api.objects
 * @abstract
 * @relatedService CuePointService
 * @requiresPermission insert,update 
 */
abstract class VidiunCuePoint extends VidiunObject implements IRelatedFilterable, IApiObjectFactory
{
	/**
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $id;

	/**
	 * @var int
	 * @filter order
	 * @readonly
	 */
	public $intId;

	/**
	 * @var VidiunCuePointType
	 * @filter eq,in
	 * @readonly
	 */
	public $cuePointType;
	
	/**
	 * @var VidiunCuePointStatus
	 * @filter eq,in
	 * @readonly
	 */
	public $status;
	
	/**
	 * @var string
	 * @filter eq,in
	 * @insertonly
	 */
	public $entryId;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $createdAt;

	/**
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $updatedAt;

	/**
	 * @var time
	 * @filter gte,lte,order
	 */
	public $triggeredAt;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $tags;

	/**
	 * Start time in milliseconds
	 * @var int 
	 * @filter gte,lte,order
	 */
	public $startTime;
	
	/**
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $userId;
	
	/**
	 * @var string
	 */
	public $partnerData;
	
	/**
	 * @var int
	 * @filter eq,in,gte,lte,order
	 */
	public $partnerSortValue;
	
	/**
	 * @var VidiunNullableBoolean
	 * @filter eq
	 */
	public $forceStop;
	
	/**
	 * @var int
	 */
	public $thumbOffset;
	
	/**
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;

	/**
	 * @var bool
	 * @readonly
	 */
	public $isMomentary;


	/**
	 * @var string
	 * @readonly
	 */
	public $copiedFrom;
	
	private static $map_between_objects = array
	(
		"id",
		"intId",
		"cuePointType" => "type",
		"status",
		"entryId",
		"partnerId",
		"createdAt",
		"updatedAt",
		"tags",
		"startTime",
		"partnerData",
		"partnerSortValue",
		"forceStop",
		"thumbOffset",
		"systemName",
		"triggeredAt",
		"isMomentary",
		"copiedFrom"
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	public function doFromObject($dbCuePoint, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbCuePoint, $responseProfile);
		
		if($this->shouldGet('userId', $responseProfile))
		{
			if($dbCuePoint->getVuserId() !== null){
				$dbVuser = vuserPeer::retrieveByPK($dbCuePoint->getVuserId());
				if($dbVuser){
					if (!vConf::hasParam('protect_userid_in_api') || !in_array($dbCuePoint->getPartnerId(), vConf::get('protect_userid_in_api')) || !in_array(vCurrentContext::getCurrentSessionType(), array(vSessionBase::SESSION_TYPE_NONE,vSessionBase::SESSION_TYPE_WIDGET)))
						$this->userId = $dbVuser->getPuserId();
				}
			}
		}
	}
	
	/*
	 * @param string $cuePointId
	 * @throw VidiunAPIException
	 */
	public function validateEntryId($cuePointId = null)
	{	
		$dbEntry = entryPeer::retrieveByPK($this->entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
			
		if($cuePointId !== null){ // update
			$dbCuePoint = CuePointPeer::retrieveByPK($cuePointId);
			if(!$dbCuePoint)
				throw new VidiunAPIException(VidiunCuePointErrors::INVALID_OBJECT_ID, $cuePointId);
				
			if($this->entryId !== null && $this->entryId != $dbCuePoint->getEntryId())
				throw new VidiunAPIException(VidiunCuePointErrors::CANNOT_UPDATE_ENTRY_ID);
		}
	}
	

	
	/*
	 * @param CuePoint $cuePoint
	 * @throw VidiunAPIException
	 */
	public function validateEndTime(CuePoint $cuePoint = null)
	{
		//setting params or default if null
		if(is_null($this->startTime) && $cuePoint && $cuePoint->getStartTime())
			$this->startTime = $cuePoint->getStartTime();

		if(is_null($this->triggeredAt) && $cuePoint && $cuePoint->getTriggeredAt())
			$this->triggeredAt = $cuePoint->getTriggeredAt();

		if ($this->isNull('endTime') && (!$cuePoint || is_null($cuePoint->getEndTime())))
			$this->endTime = $this->startTime;

		if ($this->triggeredAt && $this->isNull('duration') && (!$cuePoint || is_null($cuePoint->getDuration())))
			$this->duration = 0;

        //validate end time
		if(!is_null($this->endTime) && $this->endTime < $this->startTime)
			throw new VidiunAPIException(VidiunCuePointErrors::END_TIME_CANNOT_BE_LESS_THAN_START_TIME, $this->parentId);

		if($this->duration && $this->duration < 0)
			throw new VidiunAPIException(VidiunCuePointErrors::END_TIME_CANNOT_BE_LESS_THAN_START_TIME, $this->parentId);
		
		if($cuePoint)
		{
			$dbEntry = entryPeer::retrieveByPK($cuePoint->getEntryId());
		}
		else //add
		{ 
			$dbEntry = entryPeer::retrieveByPK($this->entryId);
		}
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
		
		if($dbEntry->getType() != entryType::LIVE_STREAM
			&& $dbEntry->getLengthInMsecs())
		{
			if($this->endTime && $dbEntry->getLengthInMsecs() < $this->endTime)
				throw new VidiunAPIException(VidiunCuePointErrors::END_TIME_IS_BIGGER_THAN_ENTRY_END_TIME, $this->endTime, $dbEntry->getLengthInMsecs());
				
			if($this->duration && $dbEntry->getLengthInMsecs() < $this->duration)
				throw new VidiunAPIException(VidiunCuePointErrors::END_TIME_IS_BIGGER_THAN_ENTRY_END_TIME, $this->duration, $dbEntry->getLengthInMsecs());
		}	
	}
	
	/*
	 * @param string $cuePointId
	 * @throw VidiunAPIException
	 */
	public function validateStartTime($cuePointId = null)
	{
		if ($this->startTime === null) {
			if((!$this->triggeredAt && !$this->isNull('duration')) || !$this->isNull('endTime'))
				throw new VidiunAPIException(VidiunCuePointErrors::END_TIME_WITHOUT_START_TIME);
			$this->startTime = 0;
		}
		
		if($this->startTime < 0)
			throw new VidiunAPIException(VidiunCuePointErrors::START_TIME_CANNOT_BE_LESS_THAN_0);
		
		if($cuePointId !== null){ //update
			$dbCuePoint = CuePointPeer::retrieveByPK($cuePointId);
			if(!$dbCuePoint)
				throw new VidiunAPIException(VidiunCuePointErrors::INVALID_OBJECT_ID, $cuePointId);
				
			$dbEntry = entryPeer::retrieveByPK($dbCuePoint->getEntryId());
		}
		else //add
		{ 
			$dbEntry = entryPeer::retrieveByPK($this->entryId);
			if (!$dbEntry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
		}
		if($dbEntry->getType() != entryType::LIVE_STREAM 
			&& !in_array($dbEntry->getSourceType(), array(EntrySourceType::VIDIUN_RECORDED_LIVE, EntrySourceType::RECORDED_LIVE))
			&& $dbEntry->getLengthInMsecs() && $dbEntry->getLengthInMsecs() < $this->startTime)
			throw new VidiunAPIException(VidiunCuePointErrors::START_TIME_IS_BIGGER_THAN_ENTRY_END_TIME, $this->startTime, $dbEntry->getLengthInMsecs());
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		$propertiesToSkip[] = 'cuePointType';
		parent::validateForInsert($propertiesToSkip);
		
		$this->validatePropertyNotNull("entryId");
		$this->validateEntryId();
		$this->validateStartTime();
				
		if($this->tags != null)
			$this->validatePropertyMaxLength("tags", CuePointPeer::MAX_TAGS_LENGTH);
	}
	
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		if($this->tags !== null)
			$this->validatePropertyMaxLength("tags", CuePointPeer::MAX_TAGS_LENGTH);
		
		if($this->entryId !== null)
			$this->validateEntryId($sourceObject->getId());
		
		if($this->startTime !== null)
			$this->validateStartTime($sourceObject->getId());
					
		$propertiesToSkip[] = 'cuePointType';
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}

	/**
	 * @param int $type
	 * @return VidiunCuePoint
	 */
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$object = VidiunPluginManager::loadObject('VidiunCuePoint', $sourceObject->getType());
		if (!$object)
		    return null;
		
		$object->fromObject($sourceObject, $responseProfile);		 
		return $object;
	}

}
