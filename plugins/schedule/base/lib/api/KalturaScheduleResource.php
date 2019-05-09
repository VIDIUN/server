<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 * @relatedService ScheduleResourceService
 * @abstract
 */
abstract class VidiunScheduleResource extends VidiunObject implements IRelatedFilterable, IApiObjectFactory
{
	/**
	 * Auto-generated unique identifier
	 * @var int
	 * @readonly
	 * @filter eq,in,notin
	 */
	public $id;

	/**
	 * @var int
	 * @filter eq,in
	 */
	public $parentId;

	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;

	/**
	 * Defines a short name
	 * @var string
	 * @filter eq
	 * @minLength 1
	 * @maxLength 256
	 */
	public $name;

	/**
	 * Defines a unique system-name
	 * @var string
	 * @minLength 1
	 * @maxLength 256
	 * @filter eq,in
	 */
	public $systemName;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var VidiunScheduleResourceStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;

	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $tags;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * Last update as Unix timestamp (In seconds)
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/*
	 * Mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array 
	 (	
	 	'id',
		'parentId',
		'partnerId',
		'name',
		'systemName',
		'description',
		'status',
		'tags',
		'createdAt',
		'updatedAt',
	 );
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
	
	abstract protected function getScheduleResourceType();
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert($propertiesToSkip)
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('name');

		if(!$this->isNull('systemName'))
		{
			$c = new Criteria();
			$c->add(ScheduleResourcePeer::SYSTEM_NAME, $this->systemName);
			$c->add(ScheduleResourcePeer::TYPE, $this->getScheduleResourceType());
			if(ScheduleResourcePeer::doCount($c))
				throw new VidiunAPIException(VidiunErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}

		if (!$this->isNull('parentId') && $this->parentId != 0 )
		{
			$scheduleResource = ScheduleResourcePeer::retrieveByPK($this->parentId);
			if (is_null($scheduleResource))
				throw new VidiunAPIException(VidiunErrors::RESOURCE_PARENT_ID_NOT_FOUND, $this->parentId);
		}
		
		return parent::validateForInsert($propertiesToSkip);
	}
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate($sourceObject, $propertiesToSkip)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		if(!$this->isNull('systemName'))
		{
			$c = new Criteria();
			$c->add(ScheduleResourcePeer::SYSTEM_NAME, $this->systemName);
			$c->add(ScheduleResourcePeer::TYPE, $this->getScheduleResourceType());
			$c->add(ScheduleResourcePeer::ID, $sourceObject->getId(), Criteria::NOT_EQUAL);
			if(ScheduleResourcePeer::doCount($c))
				throw new VidiunAPIException(VidiunErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}

		if (!$this->isNull('parentId') && $this->parentId != 0 && $this->parentId != $sourceObject->getId() )
		{
			$scheduleResource = ScheduleResourcePeer::retrieveByPK($this->parentId);
			if (is_null($scheduleResource))
				throw new VidiunAPIException(VidiunErrors::RESOURCE_PARENT_ID_NOT_FOUND, $this->parentId);
		}
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see IApiObjectFactory::getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile)
	 */
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$object = null;
	    switch($sourceObject->getType())
	    {
		    case ScheduleResourceType::LOCATION:
	    		$object = new VidiunLocationScheduleResource();
	    		break;
	    		
	    	case ScheduleResourceType::LIVE_ENTRY:
	    		$object = new VidiunLiveEntryScheduleResource();
	    		break;
	    		
	    	case ScheduleResourceType::CAMERA:
	    		$object = new VidiunCameraScheduleResource();
	    		break;
	    		
	    	default:
				$object = VidiunPluginManager::loadObject('VidiunScheduleResource', $sourceObject->getType());
				if(!$object)
				{
	    			return null;
				}
	    }
	    
	    $object->fromObject($sourceObject, $responseProfile);
	    return $object;
	}
}