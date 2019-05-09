<?php
/**
 * Event notification template service lets you create and manage event notification templates
 * @service eventNotificationTemplate
 * @package plugins.eventNotification
 * @subpackage api.services
 */
class EventNotificationTemplateService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$partnerId = $this->getPartnerId();
		if (!EventNotificationPlugin::isAllowedPartner($partnerId))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, EventNotificationPlugin::PLUGIN_NAME);
			
		$this->applyPartnerFilterForClass('EventNotificationTemplate');
	}
		
	/**
	 * This action allows for the creation of new backend event types in the system. This action requires access to the Vidiun server Admin Console. If you're looking to register to existing event types, please use the clone action instead.
	 * 
	 * @action add
	 * @param VidiunEventNotificationTemplate $eventNotificationTemplate
	 * @return VidiunEventNotificationTemplate
	 */
	public function addAction(VidiunEventNotificationTemplate $eventNotificationTemplate)
	{
		$dbEventNotificationTemplate = $eventNotificationTemplate->toInsertableObject();
		/* @var $dbEventNotificationTemplate EventNotificationTemplate */
		$dbEventNotificationTemplate->setStatus(EventNotificationTemplateStatus::ACTIVE);
		//Partner 0 cannot be impersonated, the reasong this work is because null equals to 0.
		$dbEventNotificationTemplate->setPartnerId($this->impersonatedPartnerId);
		$dbEventNotificationTemplate->save();
		
		// return the saved object
		$eventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($dbEventNotificationTemplate->getType());
		$eventNotificationTemplate->fromObject($dbEventNotificationTemplate, $this->getResponseProfile());
		return $eventNotificationTemplate;
		
	}
		
	/**
	 * This action allows registering to various backend event. Use this action to create notifications that will react to events such as new video was uploaded or metadata field was updated. To see the list of available event types, call the listTemplates action.
	 * 
	 * @action clone
	 * @param int $id source template to clone
	 * @param VidiunEventNotificationTemplate $eventNotificationTemplate overwrite configuration object
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_WRONG_TYPE
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_DUPLICATE_SYSTEM_NAME
	 * @return VidiunEventNotificationTemplate
	 */
	public function cloneAction($id, VidiunEventNotificationTemplate $eventNotificationTemplate = null)
	{
		// get the source object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);
			
		// copy into new db object
		$newDbEventNotificationTemplate = $dbEventNotificationTemplate->copy();
		
		// init new Vidiun object
		$newEventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($newDbEventNotificationTemplate->getType());
		$templateClass = get_class($newEventNotificationTemplate);
		if($eventNotificationTemplate && get_class($eventNotificationTemplate) != $templateClass && !is_subclass_of($eventNotificationTemplate, $templateClass))
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_WRONG_TYPE, $id, vPluginableEnumsManager::coreToApi('EventNotificationTemplateType', $dbEventNotificationTemplate->getType()));
		
		if ($eventNotificationTemplate)
		{
			// update new db object with the overwrite configuration
			$newDbEventNotificationTemplate = $eventNotificationTemplate->toUpdatableObject($newDbEventNotificationTemplate);
		}
		//Check uniqueness of new object's system name
		$systemNameTemplates = EventNotificationTemplatePeer::retrieveBySystemName($newDbEventNotificationTemplate->getSystemName());
		if (count($systemNameTemplates))
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_DUPLICATE_SYSTEM_NAME, $newDbEventNotificationTemplate->getSystemName());
		
		// save the new db object
		$newDbEventNotificationTemplate->setPartnerId($this->getPartnerId());
		$newDbEventNotificationTemplate->save();
	
		// return the saved object
		$newEventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($newDbEventNotificationTemplate->getType());
		$newEventNotificationTemplate->fromObject($newDbEventNotificationTemplate, $this->getResponseProfile());
		return $newEventNotificationTemplate;
		
	}
	
	/**
	 * Retrieve an event notification template object by id
	 * 
	 * @action get
	 * @param int $id 
	 * @return VidiunEventNotificationTemplate
	 * 
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 */		
	public function getAction($id)
	{
		// get the object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);
			
		// return the found object
		$eventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($dbEventNotificationTemplate->getType());
		$eventNotificationTemplate->fromObject($dbEventNotificationTemplate, $this->getResponseProfile());
		return $eventNotificationTemplate;
	}
	

	/**
	 * Update an existing event notification template object
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunEventNotificationTemplate $eventNotificationTemplate
	 * @return VidiunEventNotificationTemplate
	 *
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 */	
	public function updateAction($id, VidiunEventNotificationTemplate $eventNotificationTemplate)
	{
		// get the object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);
		
		// save the object
		$dbEventNotificationTemplate = $eventNotificationTemplate->toUpdatableObject($dbEventNotificationTemplate);
		$dbEventNotificationTemplate->save();
	
		// return the saved object
		$eventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($dbEventNotificationTemplate->getType());
		$eventNotificationTemplate->fromObject($dbEventNotificationTemplate, $this->getResponseProfile());
		return $eventNotificationTemplate;
	}

	/**
	 * Update event notification template status by id
	 * 
	 * @action updateStatus
	 * @param int $id
	 * @param VidiunEventNotificationTemplateStatus $status
	 * @return VidiunEventNotificationTemplate
	 * 
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 */
	function updateStatusAction($id, $status)
	{
		// get the object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);

		if($status == EventNotificationTemplateStatus::ACTIVE)
		{
			//Check uniqueness of new object's system name
			$systemNameTemplates = EventNotificationTemplatePeer::retrieveBySystemName($dbEventNotificationTemplate->getSystemName());
			if (count($systemNameTemplates))
				throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_DUPLICATE_SYSTEM_NAME, $dbEventNotificationTemplate->getSystemName());
		}	
		
		// save the object
		$dbEventNotificationTemplate->setStatus($status);
		$dbEventNotificationTemplate->save();
	
		// return the saved object
		$eventNotificationTemplate = VidiunEventNotificationTemplate::getInstanceByType($dbEventNotificationTemplate->getType());
		$eventNotificationTemplate->fromObject($dbEventNotificationTemplate, $this->getResponseProfile());
		return $eventNotificationTemplate;
	}

	/**
	 * Delete an event notification template object
	 * 
	 * @action delete
	 * @param int $id 
	 *
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 */		
	public function deleteAction($id)
	{
		// get the object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);

		// set the object status to deleted
		$dbEventNotificationTemplate->setStatus(EventNotificationTemplateStatus::DELETED);
		$dbEventNotificationTemplate->save();
	}
	
	/**
	 * list event notification template objects
	 * 
	 * @action list
	 * @param VidiunEventNotificationTemplateFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEventNotificationTemplateListResponse
	 */
	public function listAction(VidiunEventNotificationTemplateFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunEventNotificationTemplateFilter();
			
		if (!$pager)
			$pager = new VidiunFilterPager ();

		$eventNotificationTemplateFilter = new EventNotificationTemplateFilter();
		$filter->toObject($eventNotificationTemplateFilter);

		$c = new Criteria();
		$eventNotificationTemplateFilter->attachToCriteria($c);
		$count = EventNotificationTemplatePeer::doCount($c);
		
		$pager->attachToCriteria ( $c );
		$list = EventNotificationTemplatePeer::doSelect($c);
		
		$response = new VidiunEventNotificationTemplateListResponse();
		$response->objects = VidiunEventNotificationTemplateArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}

	/**
	 * @action listByPartner
	 * @param VidiunPartnerFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEventNotificationTemplateListResponse
	 */
	public function listByPartnerAction(VidiunPartnerFilter $filter = null, VidiunFilterPager $pager = null)
	{
		$c = new Criteria();
		
		if (!is_null($filter))
		{
			
			$partnerFilter = new partnerFilter();
			$filter->toObject($partnerFilter);
			$partnerFilter->set('_gt_id', -1);
			
			$partnerCriteria = new Criteria();
			$partnerFilter->attachToCriteria($partnerCriteria);
			$partnerCriteria->setLimit(1000);
			$partnerCriteria->clearSelectColumns();
			$partnerCriteria->addSelectColumn(PartnerPeer::ID);
			$stmt = PartnerPeer::doSelectStmt($partnerCriteria);
			
			if($stmt->rowCount() < 1000) // otherwise, it's probably all partners
			{
				$partnerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
				$c->add(EventNotificationTemplatePeer::PARTNER_ID, $partnerIds, Criteria::IN);
			}
		}
			
		if (is_null($pager))
			$pager = new VidiunFilterPager();
			
		$c->addDescendingOrderByColumn(EventNotificationTemplatePeer::CREATED_AT);
		
		$totalCount = EventNotificationTemplatePeer::doCount($c);
		$pager->attachToCriteria($c);
		$list = EventNotificationTemplatePeer::doSelect($c);
		$newList = VidiunEventNotificationTemplateArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunEventNotificationTemplateListResponse();
		$response->totalCount = $totalCount;
		$response->objects = $newList;
		return $response;
	}
	
	/**
	 * Dispatch event notification object by id
	 * 
	 * @action dispatch
	 * @param int $id 
	 * @param VidiunEventNotificationScope $scope
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_DISPATCH_DISABLED
	 * @throws VidiunEventNotificationErrors::EVENT_NOTIFICATION_DISPATCH_FAILED
	 * @return int
	 */		
	public function dispatchAction($id, VidiunEventNotificationScope $scope)
	{
		// get the object
		$dbEventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($id);
		if (!$dbEventNotificationTemplate)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $id);
			
		if(!$dbEventNotificationTemplate->getManualDispatchEnabled())
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_DISPATCH_DISABLED, $id);

		$jobId = $dbEventNotificationTemplate->dispatch($scope->toObject());
		if(!$jobId)
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_DISPATCH_FAILED, $id);
			
		return $jobId;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
		
		switch ($this->actionName)
		{
			case 'clone':
				return $this->partnerGroup . ',0';
			case 'listTemplates':
				return '0';
		}
			
		return $this->partnerGroup;
	}
	
	/**
	 * Action lists the template partner event notification templates.
	 * @action listTemplates
	 * 
	 * @param VidiunEventNotificationTemplateFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunEventNotificationTemplateListResponse
	 */
	public function listTemplatesAction (VidiunEventNotificationTemplateFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunEventNotificationTemplateFilter();
			
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		$coreFilter = new EventNotificationTemplateFilter();
		$filter->toObject($coreFilter);
		
		$criteria = new Criteria();
		$coreFilter->attachToCriteria($criteria);
		$criteria->add(EventNotificationTemplatePeer::PARTNER_ID, PartnerPeer::GLOBAL_PARTNER);
		$count = EventNotificationTemplatePeer::doCount($criteria);
		
		$pager->attachToCriteria($criteria);
		$results = EventNotificationTemplatePeer::doSelect($criteria);
		
		$response = new VidiunEventNotificationTemplateListResponse();
		$response->objects = VidiunEventNotificationTemplateArray::fromDbArray($results, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}
}
