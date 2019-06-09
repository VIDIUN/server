<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects
 */
class VidiunScheduledTaskProfile extends VidiunObject implements IFilterable
{	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,order
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var VidiunScheduledTaskProfileStatus
	 * @filter eq,in
	 */
	public $status;

	/**
	 * The type of engine to use to list objects using the given "objectFilter"
	 *
	 * @var VidiunObjectFilterEngineType
	 */
	public $objectFilterEngineType;

	/**
	 * A filter object (inherits VidiunFilter) that is used to list objects for scheduled tasks
	 *
	 * @var VidiunFilter
	 */
	public $objectFilter;

	/**
	 * A list of tasks to execute on the founded objects
	 *
	 * @var VidiunObjectTaskArray
	 */
	public $objectTasks;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * @var time
	 * @filter gte,lte,order,lteornull
	 */
	public $lastExecutionStartedAt;

	/**
	 * The maximum number of result count allowed to be processed by this profile per execution
	 *
	 * @var int
	 */
	public $maxTotalCountAllowed;

	/*
	 */
	private static $map_between_objects = array(
		'id',
		'partnerId',
		'name',
		'systemName',
		'description',
		'status',
		'objectFilterEngineType',
		'objectFilter',
		'objectTasks',
		'createdAt',
		'updatedAt',
		'lastExecutionStartedAt',
		'maxTotalCountAllowed',
	);
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toInsertableObject($objectToFill = null, $propertiesToSkip = array())
	{
		if (is_null($this->status))
			$this->status = VidiunScheduledTaskProfileStatus::DISABLED;

		return parent::toInsertableObject($objectToFill, $propertiesToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyMinLength('name', 3, false);
		$this->validatePropertyMinLength('systemName', 3, true);
		$this->validatePropertyNotNull('objectFilterEngineType');
		$this->validatePropertyNotNull('objectFilter');
		$this->validatePropertyNotNull('objectTasks');
		$this->validatePropertyNotNull('maxTotalCountAllowed');
		foreach($this->objectTasks as $objectTask)
		{
			/* @var VidiunObjectTask $objectTask */
			$objectTask->validateForInsert(array('type'));
		}
		parent::validateForInsert($propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyMinLength('name', 3, true);
		$this->validatePropertyMinLength('systemName', 3, true);

		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new ScheduledTaskProfile();

		$dbObject = parent::toObject($dbObject, $propertiesToSkip);
		if (!is_null($this->objectFilter))
			$dbObject->setObjectFilterApiType(get_class($this->objectFilter));
		return $dbObject;
	}

	/**
	 * @param ScheduledTaskProfile $srcObj
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);
		$this->objectTasks = VidiunObjectTaskArray::fromDbArray($srcObj->getObjectTasks());
		$filterType = $srcObj->getObjectFilterApiType();
		if (!class_exists($filterType))
		{
			VidiunLog::err(sprintf('Class %s not found, cannot initiate object filter instance', $filterType));
			$this->objectFilter = new VidiunFilter();
		}
		else
		{
			$this->objectFilter = new $filterType();
		}

		$this->objectFilter->fromObject($srcObj->getObjectFilter());
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
}