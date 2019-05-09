<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.objects
 */
class VidiunSystemPartnerOveragedLimit extends VidiunSystemPartnerLimit
{
	/**
	 * @var float
	 */
	public $overagePrice;
	
	/**
	 * @var float
	 */
	public $overageUnit;
	
	/**
	 * @param VidiunSystemPartnerLimitType $type
	 * @param Partner $partner
	 * @return VidiunSystemPartnerLimit
	 */
	public static function fromPartner($type, Partner $partner)
	{
		$limit = new VidiunSystemPartnerOveragedLimit();
		$limit->type = $type;
		
		switch($type)
		{
			case VidiunSystemPartnerLimitType::ENTRIES:
				$limit->max = $partner->getEntriesQuota();
				$limit->overagePrice = $partner->getEntriesOveragePrice();
				$limit->overageUnit = $partner->getEntriesOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STREAM_ENTRIES:
				$limit->max = $partner->getStreamEntriesQuota();
				$limit->overagePrice = $partner->getStreamEntriesOveragePrice();
				$limit->overageUnit = $partner->getStreamEntriesOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_BANDWIDTH:
				$limit->max = $partner->getBandwidthQuota();
				$limit->overagePrice = $partner->getBandwidthOveragePrice();
				$limit->overageUnit = $partner->getBandwidthOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::PUBLISHERS:
				$limit->max = $partner->getPublishersQuota();
				$limit->overagePrice = $partner->getPublishersOveragePrice();
				$limit->overageUnit = $partner->getPublishersOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::LOGIN_USERS:
				$limit->max = $partner->getLoginUsersQuota();
				$limit->overagePrice = $partner->getLoginUsersOveragePrice();
				$limit->overageUnit = $partner->getLoginUsersOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::ADMIN_LOGIN_USERS:
				$limit->max = $partner->getAdminLoginUsersQuota();
				$limit->overagePrice = $partner->getAdminLoginUsersOveragePrice();
				$limit->overageUnit = $partner->getAdminLoginUsersOverageUnit();
				break;
			
			case VidiunSystemPartnerLimitType::BULK_SIZE:
				$limit->max = $partner->getMaxBulkSize();
				$limit->overagePrice = $partner->getMaxBulkSizeOveragePrice();
				$limit->overageUnit = $partner->getMaxBulkSizeOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE:
				$limit->max = $partner->getMonthlyStorage();
				$limit->overagePrice = $partner->getMonthlyStorageOveragePrice();
				$limit->overageUnit = $partner->getMonthlyStorageOverageUnit();
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE_AND_BANDWIDTH:
				$limit->max = $partner->getMonthlyStorageAndBandwidth();
				$limit->overagePrice = $partner->getMonthlyStorageAndBandwidthOveragePrice();
				$limit->overageUnit = $partner->getMonthlyStorageAndBandwidthOverageUnit();
				break;	

			case VidiunSystemPartnerLimitType::END_USERS:
				$limit->max = $partner->getEndUsers();
				$limit->overagePrice = $partner->getEndUsersOveragePrice();
				$limit->overageUnit = $partner->getEndUsersOverageUnit();
				break;

			default:
				return parent::fromPartner($type, $partner);
		}
		
		return $limit;
	} 

	public function validate()
	{
		switch($this->type)
		{
			case VidiunSystemPartnerLimitType::ENTRIES:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STREAM_ENTRIES:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_BANDWIDTH:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::PUBLISHERS:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::LOGIN_USERS:
				$this->validatePropertyMinValue('max', 1, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::ADMIN_LOGIN_USERS:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
			
			case VidiunSystemPartnerLimitType::BULK_SIZE:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;

			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;

			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE_AND_BANDWIDTH:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::END_USERS:
				$this->validatePropertyMinValue('max', 0, true);
				$this->validatePropertyMinValue('overagePrice', 0, true);
				$this->validatePropertyMinValue('overageUnit', 0, true);
				break;	
				
		}
		parent::validate();
	}
	
	/**
	 * @param Partner $partner
	 */
	public function apply(Partner $partner)
	{
		if($this->isNull('max'))
			$this->max = null;
			
		if($this->isNull('overagePrice'))
			$this->overagePrice = null;
			
		if($this->isNull('overageUnit'))
			$this->overageUnit = null;
			
		switch($this->type)
		{
			case VidiunSystemPartnerLimitType::ENTRIES:
				$partner->setEntriesQuota($this->max);
				$partner->setEntriesOveragePrice($this->overagePrice);
				$partner->setEntriesOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STREAM_ENTRIES:
				$partner->setStreamEntriesQuota($this->max);
				$partner->setStreamEntriesOveragePrice($this->overagePrice);
				$partner->setStreamEntriesOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_BANDWIDTH:
				$partner->setBandwidthQuota($this->max);
				$partner->setBandwidthOveragePrice($this->overagePrice);
				$partner->setBandwidthOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::PUBLISHERS:
				$partner->setPublishersQuota($this->max);
				$partner->setPublishersOveragePrice($this->overagePrice);
				$partner->setPublishersOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::LOGIN_USERS:
				$partner->setLoginUsersQuota($this->max);
				$partner->setLoginUsersOveragePrice($this->overagePrice);
				$partner->setLoginUsersOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::ADMIN_LOGIN_USERS:
				$partner->setAdminLoginUsersQuota($this->max);
				$partner->setAdminLoginUsersOveragePrice($this->overagePrice);
				$partner->setAdminLoginUsersOverageUnit($this->overageUnit);
				break;
			
			case VidiunSystemPartnerLimitType::BULK_SIZE:
				$partner->setMaxBulkSize($this->max);
				$partner->setMaxBulkSizeOveragePrice($this->overagePrice);
				$partner->setMaxBulkSizeOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE:
				$partner->setMonthlyStorage($this->max);
				$partner->setMonthlyStorageOveragePrice($this->overagePrice);
				$partner->setMonthlyStorageOverageUnit($this->overageUnit);
				break;
				
			case VidiunSystemPartnerLimitType::MONTHLY_STORAGE_AND_BANDWIDTH:
				$partner->setMonthlyStorageAndBandwidth($this->max);
				$partner->setMonthlyStorageAndBandwidthOveragePrice($this->overagePrice);
				$partner->setMonthlyStorageAndBandwidthOverageUnit($this->overageUnit);
				break;	

			case VidiunSystemPartnerLimitType::END_USERS:
				$partner->setEndUsers($this->max);
				$partner->setEndUsersOveragePrice($this->overagePrice);
				$partner->setEndUsersOverageUnit($this->overageUnit);
				break;	
				
		}
		parent::apply($partner);
	} 
}