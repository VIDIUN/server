<?php

/**
 * delivery service is used to control delivery objects
 *
 * @service deliveryProfile
 * @package api
 * @subpackage services
 */
class DeliveryProfileService extends VidiunBaseService
{
	
	/**
	 * Add new delivery.
	 *
	 * @action add
	 * @param VidiunDeliveryProfile $delivery
	 * @return VidiunDeliveryProfile
	 */
	function addAction(VidiunDeliveryProfile $delivery)
	{
		$dbVidiunDelivery = $delivery->toInsertableObject();
		$dbVidiunDelivery->setPartnerId($this->getPartnerId());
		$dbVidiunDelivery->setParentId(0);
		$dbVidiunDelivery->save();
		
		$delivery = VidiunDeliveryProfileFactory::getDeliveryProfileInstanceByType($dbVidiunDelivery->getType());
		$delivery->fromObject($dbVidiunDelivery, $this->getResponseProfile());
		return $delivery;
	}
	
	/**
	 * Update existing delivery profile
	 *
	 * @action update
	 * @param string $id
	 * @param VidiunDeliveryProfile $delivery
	 * @return VidiunDeliveryProfile
	 */
	function updateAction( $id , VidiunDeliveryProfile $delivery )
	{
		DeliveryProfilePeer::setUseCriteriaFilter(false);
		$dbDelivery = DeliveryProfilePeer::retrieveByPK($id);
		DeliveryProfilePeer::setUseCriteriaFilter(true);
		if (!$dbDelivery)
			throw new VidiunAPIException(VidiunErrors::DELIVERY_ID_NOT_FOUND, $id);
		
		// Don't allow to update default delivery profiles from the outside
		if($dbDelivery->getIsDefault())
			throw new VidiunAPIException(VidiunErrors::DELIVERY_UPDATE_ISNT_ALLOWED, $id);
		
		$delivery->toUpdatableObject($dbDelivery);
		$dbDelivery->save();
		
		$delivery = VidiunDeliveryProfileFactory::getDeliveryProfileInstanceByType($dbDelivery->getType());
		$delivery->fromObject($dbDelivery, $this->getResponseProfile());
		return $delivery;
	}
	
	/**
	* Get delivery by id
	*
	* @action get
	* @param string $id
	* @return VidiunDeliveryProfile
	*/
	function getAction( $id )
	{
		DeliveryProfilePeer::setUseCriteriaFilter(false);
		$dbDelivery = DeliveryProfilePeer::retrieveByPK($id);
		DeliveryProfilePeer::setUseCriteriaFilter(true);
		
		if (!$dbDelivery)
			throw new VidiunAPIException(VidiunErrors::DELIVERY_ID_NOT_FOUND, $id);
			
		$delivery = VidiunDeliveryProfileFactory::getDeliveryProfileInstanceByType($dbDelivery->getType());
		$delivery->fromObject($dbDelivery, $this->getResponseProfile());
		return $delivery;
	}
	
	/**
	* Add delivery based on existing delivery.
	* Must provide valid sourceDeliveryId
	*
	* @action clone
	* @param int $deliveryId
	* @return VidiunDeliveryProfile
	*/
	function cloneAction( $deliveryId )
	{
		$dbDelivery = DeliveryProfilePeer::retrieveByPK( $deliveryId );
		
		if ( ! $dbDelivery )
			throw new VidiunAPIException ( APIErrors::DELIVERY_ID_NOT_FOUND , $deliveryId );
		
		$className = get_class($dbDelivery);
		$class = new ReflectionClass($className);
		$dbVidiunDelivery = $class->newInstanceArgs(array());
		$dbVidiunDelivery = $dbDelivery->cloneToNew ( $dbVidiunDelivery );
		
		$delivery = VidiunDeliveryProfileFactory::getDeliveryProfileInstanceByType($dbVidiunDelivery->getType());
		$delivery->fromObject($dbVidiunDelivery, $this->getResponseProfile());
		return $delivery;
	}
	
	/**
	* Retrieve a list of available delivery depends on the filter given
	*
	* @action list
	* @param VidiunDeliveryProfileFilter $filter
	* @param VidiunFilterPager $pager
	* @return VidiunDeliveryProfileListResponse
	*/
	function listAction( VidiunDeliveryProfileFilter $filter=null , VidiunFilterPager $pager=null)
	{
		if (!$filter)
			$filter = new VidiunDeliveryProfileFilter();

		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$delivery = new DeliveryProfileFilter();
		$filter->toObject($delivery);

		DeliveryProfilePeer::setUseCriteriaFilter(false);
		
		$c = new Criteria();
		$c->add(DeliveryProfilePeer::PARTNER_ID, array(0, vCurrentContext::getCurrentPartnerId()), Criteria::IN);
		$delivery->attachToCriteria($c);
		
		$totalCount = DeliveryProfilePeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = DeliveryProfilePeer::doSelect($c);
		
		DeliveryProfilePeer::setUseCriteriaFilter(true);
		
		$objects = VidiunDeliveryProfileArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunDeliveryProfileListResponse();
		$response->objects = $objects;
		$response->totalCount = $totalCount;
		return $response;    
	}
}

