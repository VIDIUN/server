<?php

/**
 * Skeleton subclass for representing a row from the 'schedule_event' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.schedule
 * @subpackage model
 */
abstract class ScheduleEvent extends BaseScheduleEvent implements IRelatedObject, IIndexable
{
	const CUSTOM_DATA_FIELD_RECURRENCE = 'recurrence';
	const CUSTOM_DATA_FIELD_OWNER_ID = 'ownerId';

	const RESOURCE_PARENT_SEARCH_PERFIX = 'r';
	const RESOURCES_INDEXED_FIELD_PREFIX = 'pid';
	
	public function __construct() 
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or equivalent initialization method).
	 * @see __construct()
	 */
	public function applyDefaultValues()
	{
		$this->setSequence(1);
	}
	
	/* (non-PHPdoc)
	 * @see BaseScheduleEvent::preInsert()
	 */
	public function preInsert(PropelPDO $con = null)
	{
		$this->setStatus(ScheduleEventStatus::ACTIVE);
		$this->setPartnerId(vCurrentContext::getCurrentPartnerId());
		
		if(!$this->getParentId())
		{
			$this->setOwnerId(vCurrentContext::$vs_uid);
			$this->incrementSequence();
		}
		
		if($this->getRecurrenceType() != ScheduleEventRecurrenceType::RECURRENCE)
		{
			if(is_null($this->getClassificationType()))
			{
				$this->setClassificationType(ScheduleEventClassificationType::PUBLIC_EVENT);
			}
		}
		$this->setCustomDataObj();
    	
		return parent::preInsert($con);
	}
	
	/**
	 * {@inheritDoc}
	 * @see BaseScheduleEvent::postInsert()
	 */
	public function postInsert(PropelPDO $con = null)
	{
		parent::postInsert($con);
	
		if (!$this->alreadyInSave)
			vEventsManager::raiseEvent(new vObjectAddedEvent($this));
	}
	
	/**
	 * {@inheritDoc}
	 * @see BaseScheduleEvent::postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
			return parent::postUpdate($con);
		
		$objectUpdated = $this->isModified();
		$objectDeleted = false;
		if($this->isColumnModified(ScheduleEventPeer::STATUS) && $this->getStatus() == ScheduleEventStatus::DELETED) {
			$objectDeleted = true;
		}
			
		$ret = parent::postUpdate($con);
		
		if ($objectDeleted)
		{
			vEventsManager::raiseEvent(new vObjectDeletedEvent($this));
		}

		if($objectUpdated)
		{
		    vEventsManager::raiseEvent(new vObjectUpdatedEvent($this));
		}
			
		return $ret;
	}
	
	
	/* (non-PHPdoc)
	 * @see BaseScheduleEvent::preSave()
	 */
	public function preSave(PropelPDO $con = null)
	{
		if($this->getRecurrenceType() != ScheduleEventRecurrenceType::RECURRING && $this->getDuration() != $this->getEndDate(null) - $this->getStartDate(null))
		{
			$this->setDuration($this->getEndDate(null) - $this->getStartDate(null));
		}
    	
		return parent::preSave($con);
	}
	
	public function incrementSequence()
	{
		$this->setSequence(vDataCenterMgr::incrementVersion($this->getSequence()));
	}
	
	/**
	 * @param string $v
	 */
	public function setOwnerId($puserId)
	{
		$vuser = vuserPeer::createVuserForPartner(vCurrentContext::getCurrentPartnerId(), $puserId, vCurrentContext::$is_admin_session);
		$this->setOwnerVuserId($vuser->getId());
		$this->putInCustomData(self::CUSTOM_DATA_FIELD_OWNER_ID, $puserId);
	}
	
	/**
	 * @return string
	 */
	public function getOwnerId()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_FIELD_OWNER_ID);
	}
	
	/**
	 * @param vScheduleEventRecurrence $v
	 */
	public function setRecurrence(vScheduleEventRecurrence $v)
	{
		$this->putInCustomData(self::CUSTOM_DATA_FIELD_RECURRENCE, $v);
	}

	public function deleteRecurrence()
	{
		$this->removeFromCustomData(self::CUSTOM_DATA_FIELD_RECURRENCE);
	}

	/**
	 * @return vScheduleEventRecurrence
	 */
	public function getRecurrence()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_FIELD_RECURRENCE);
	}
	
	/**
	 * {@inheritDoc}
	 * @see IIndexable::getIntId()
	 */
	public function getIntId()
	{
		return $this->getId();
	}
	
	/**
	 * {@inheritDoc}
	 * @see IIndexable::getEntryId()
	 */
	public function getEntryId()
	{
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see IIndexable::getIndexObjectName()
	 */
	public function getIndexObjectName()
	{
		return 'ScheduleEventIndex';
	}
	
	/**
	 * {@inheritDoc}
	 * @see IIndexable::indexToSearchIndex()
	 */
	public function indexToSearchIndex()
	{
		vEventsManager::raiseEventDeferred(new vObjectReadyForIndexEvent($this));
	}
	
	public function getSphinxIndexName()
	{
		return kSphinxSearchManager::getSphinxIndexName(ScheduleEventIndex::getObjectIndexName());
	}
	
	public function getEntryIds()
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 * @see IIndexable::getTemplateEntryId()
	 */
	public function getTemplateEntryId()
	{
		return null;
	}

	public function getCategoryIdsForIndex()
	{
		return '';
	}

	public function getResourceIdsForIndex()
	{
		$resources = ScheduleEventResourcePeer::retrieveByEventIdOrItsParentId($this->getId());

		$index = array();
		foreach($resources as $resource)
		{
			/* @var $resource ScheduleEventResource */
				
			$index[] = $resource->getResourceId();
				
			$fullParentIds = $resource->getFullParentIds();
			foreach($fullParentIds as $parentId)
			{
				$index[] = self::RESOURCE_PARENT_SEARCH_PERFIX . $parentId;
			}
		}
	
		$index = array_unique($index);
	
		return self::RESOURCES_INDEXED_FIELD_PREFIX . $this->getPartnerId() . " " .  implode(' ', $index);
	}

	public function getTemplateEntryCategoriesIdsForIndex()
	{
		return '';
	}

	public function getResourceSystemNamesForIndex()
	{
		$eventResources = ScheduleEventResourcePeer::retrieveByEventId($this->getId());

		$resourceIds = array();
		$system_names = array();

		foreach ($eventResources as $eventResource)
		{
			$resourceIds[] = $eventResource->getResourceId();
		}

		$resources = ScheduleResourcePeer::retrieveByPKs($resourceIds);
		foreach ($resources as $resource)
		{
			if ($resource != null)
			{
				$resourceSystemName = $resource->getSystemName();
				if ($resourceSystemName != null)
				{
					$resourceSystemName = mySearchUtils::getMd5EncodedString($resourceSystemName);
					$system_names[] = $resourceSystemName;
				}
			}
		}
		return implode(' ', $system_names);
	}

	public function getSummary()
	{
		if (parent::getSummary())
			return parent::getSummary();
		if ($this->parent_id)
		{
			$parentObj = ScheduleEventPeer::retrieveByPK($this->parent_id);
			if ($parentObj)
				return $parentObj->getSummary();
		}
	}

	public static function getEventValues($scheduleEvents, $field)
	{
		$fieldVals = array();
		foreach($scheduleEvents as $scheduleEvent) {
			/* @var $scheduleEvent ScheduleEvent */
			$fieldVals[] = $scheduleEvent->$field(null);
		}
		return $fieldVals;
	}

	public function getCacheInvalidationKeys()
	{
		return array("scheduleEvent:id".strtolower($this->getId()));
	}

	public function getBlackoutConflicts()
	{
		return array();
	}
	
	public function getSphinxMatchOptimizations() {
		$objectName = $this->getIndexObjectName();
		return $objectName::getSphinxMatchOptimizations($this);
	}
	
} // ScheduleEvent
