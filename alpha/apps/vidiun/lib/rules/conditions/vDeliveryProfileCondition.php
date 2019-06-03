<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vDeliveryProfileCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::DELIVERY_PROFILE);
		parent::__construct($not);
	}

	/**
	 * The delivery ids that are accepted by this condition
	 * 
	 * @var array
	 */
	protected $deliveryProfileIds = array();
	
	/**
	 * @param array $deliveryProfileIds
	 */
	public function setDeliveryProfileIds(array $deliveryProfileIds)
	{
		$this->deliveryProfileIds = $deliveryProfileIds;
	}
	
	/**
	 * @return array
	 */
	function getDeliveryProfileIds()
	{
		return $this->deliveryProfileIds;
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$profileIds = array();
		foreach ($this->deliveryProfileIds as $profileId)
		{
			$profileIds[] = $profileId->getValue();
		}

		VidiunLog::debug("Delivery profile ids [".print_r($profileIds, true)."]");
		$requestOrigin = @$_SERVER['HTTP_X_FORWARDED_HOST'];
		if(!$requestOrigin)
			$requestOrigin = @$_SERVER['HTTP_HOST'];
		$deliveryProfiles = DeliveryProfilePeer::retrieveByPKs($profileIds);
		foreach ($deliveryProfiles as $deliveryProfile)
		{
			/**
			 * @var DeliveryProfile $deliveryProfile
			 */
			$recognizer = $deliveryProfile->getRecognizer();
			if ($recognizer && $recognizer->isRecognized($requestOrigin))
			{
				return true;
			}
		}
		return false;
	}

}
