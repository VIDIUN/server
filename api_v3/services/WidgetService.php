<?php

/**
 * widget service for full widget management
 *
 * @service widget
 * @package api
 * @subpackage services
 */
class WidgetService extends VidiunBaseService 
{
	// use initService to add a peer to the partner filter
	/**
	 * @ignore
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('widget'); 	
	}

	/**
	 * Add new widget, can be attached to entry or vshow
	 * SourceWidget is ignored.
	 * 
	 * @action add
	 * @param VidiunWidget $widget
	 * @return VidiunWidget
	 */
	function addAction(VidiunWidget $widget)
	{
		if ($widget->sourceWidgetId === null && $widget->uiConfId === null)
		{
			throw new VidiunAPIException(VidiunErrors::SOURCE_WIDGET_OR_UICONF_REQUIRED);
		}
		
		if ($widget->sourceWidgetId !== null)
		{
			$sourceWidget = widgetPeer::retrieveByPK($widget->sourceWidgetId);
			if (!$sourceWidget) 
				throw new VidiunAPIException(VidiunErrors::SOURCE_WIDGET_NOT_FOUND, $widget->sourceWidgetId);
				
			if ($widget->uiConfId === null)
				$widget->uiConfId = $sourceWidget->getUiConfId();
		}
		
		if ($widget->uiConfId !== null)
		{
			$uiConf = uiConfPeer::retrieveByPK($widget->uiConfId);
			if (!$uiConf)
				throw new VidiunAPIException(VidiunErrors::UICONF_ID_NOT_FOUND, $widget->uiConfId);
		}
		
		if(!is_null($widget->enforceEntitlement) && $widget->enforceEntitlement == false && vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_DISABLE_ENTITLEMENT_FOR_WIDGET_WHEN_ENTITLEMENT_ENFORCEMENT_ENABLE);
		
		if ($widget->entryId !== null)
		{
			$entry = entryPeer::retrieveByPK($widget->entryId);
			if (!$entry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $widget->entryId);
		}
		elseif ($widget->enforceEntitlement != null && $widget->enforceEntitlement == false)
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_DISABLE_ENTITLEMENT_WITH_NO_ENTRY_ID);
		}
		
		$dbWidget = $widget->toInsertableWidget();
		$dbWidget->setPartnerId($this->getPartnerId());
		$dbWidget->setSubpId($this->getPartnerId() * 100);
		$widgetId = $dbWidget->calculateId($dbWidget);

		$dbWidget->setId($widgetId);
		
		if ($entry && $entry->getType() == entryType::PLAYLIST)
			$dbWidget->setIsPlayList(true);
			
		$dbWidget->save();
		$savedWidget = widgetPeer::retrieveByPK($widgetId);
		
		$widget = new VidiunWidget(); // start from blank
		$widget->fromObject($savedWidget, $this->getResponseProfile());
		
		return $widget;
	}

	/**
 	 * Update existing widget
 	 * 
	 * @action update
	 * @param string $id 
	 * @param VidiunWidget $widget
	 * @return VidiunWidget
	 */	
	function updateAction( $id , VidiunWidget $widget )
	{
		$dbWidget = widgetPeer::retrieveByPK( $id );
		
		if ( ! $dbWidget )
			throw new VidiunAPIException ( APIErrors::INVALID_WIDGET_ID , $id );
		
		if(!is_null($widget->enforceEntitlement) && $widget->enforceEntitlement == false && vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_DISABLE_ENTITLEMENT_FOR_WIDGET_WHEN_ENTITLEMENT_ENFORCEMENT_ENABLE);
		
		if ($widget->entryId !== null)
		{
			$entry = entryPeer::retrieveByPK($widget->entryId);
			if (!$entry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $widget->entryId);
		}
		elseif ($widget->enforceEntitlement != null && $widget->enforceEntitlement == false)
		{
			throw new VidiunAPIException(VidiunErrors::CANNOT_DISABLE_ENTITLEMENT_WITH_NO_ENTRY_ID);
		}
			
		$widgetUpdate = $widget->toUpdatableWidget();
		
		if ($entry && $entry->getType() == entryType::PLAYLIST)
		{
			$dbWidget->setIsPlayList(true);
		}
		else 
		{
			$dbWidget->setIsPlayList(false);
		}

		$allow_empty = true ; // TODO - what is the policy  ? 
		baseObjectUtils::autoFillObjectFromObject ( $widgetUpdate , $dbWidget , $allow_empty );
		
		$dbWidget->save();
		// TODO: widget in cache, should drop from cache

		$widget->fromObject($dbWidget, $this->getResponseProfile());
		
		return $widget;
	}

	/**
	 * Get widget by id
	 *  
	 * @action get
	 * @param string $id 
	 * @return VidiunWidget
	 * @vsOptional
	 */		
	function getAction( $id )
	{
		$dbWidget = widgetPeer::retrieveByPK( $id );

		if ( ! $dbWidget )
			throw new VidiunAPIException ( APIErrors::INVALID_WIDGET_ID , $id );
		$widget = new VidiunWidget();
		$widget->fromObject($dbWidget, $this->getResponseProfile());
		
		return $widget;
	}

	/**
	 * Add widget based on existing widget.
	 * Must provide valid sourceWidgetId
	 * 
	 * @action clone
	 * @param VidiunWidget $widget
	 * @return VidiunWidget
	 */		
	function cloneAction( VidiunWidget $widget )
	{
		$dbWidget = widgetPeer::retrieveByPK( $widget->sourceWidgetId );
		
		if ( ! $dbWidget )
			throw new VidiunAPIException ( APIErrors::INVALID_WIDGET_ID , $widget->sourceWidgetId );

		$newWidget = widget::createWidgetFromWidget( $dbWidget , $widget->vshowId, $widget->entryId, $widget->uiConfId ,
			null , $widget->partnerData , $widget->securityType );
		if ( !$newWidget )
			throw new VidiunAPIException ( APIErrors::INVALID_VSHOW_AND_ENTRY_PAIR , $widget->vshowId, $widget->entryId );

		$widget = new VidiunWidget;
		$widget->fromObject($newWidget, $this->getResponseProfile());
		return $widget;
	}
	
	/**
	 * Retrieve a list of available widget depends on the filter given
	 * 
	 * @action list
	 * @param VidiunWidgetFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunWidgetListResponse
	 */		
	function listAction( VidiunWidgetFilter $filter=null , VidiunFilterPager $pager=null)
	{
		if (!$filter)
			$filter = new VidiunWidgetFilter;
			
		$widgetFilter = new widgetFilter ();
		$filter->toObject( $widgetFilter );
		
		$c = new Criteria();
		$widgetFilter->attachToCriteria( $c );
		
		$totalCount = widgetPeer::doCount( $c );
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = widgetPeer::doSelect( $c );
		
		$newList = VidiunWidgetArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunWidgetListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}
