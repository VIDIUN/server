<?php


/**
 * Skeleton subclass for representing a row from the 'vendor_catalog_item' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.reach
 * @subpackage model
 */
class VendorCatalogItem extends BaseVendorCatalogItem implements IRelatedObject 
{

	/**
	 * Initializes internal state of VendorCatalogItem object.
	 * @see        parent::__construct()
	 */
	public function __construct()
	{
		// Make sure that parent constructor is always invoked, since that
		// is where any default values for this object are set.
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
	}
	
	const CUSTOM_DATA_PRICING = "pricing";
	
	public function setPricing($pricing)
	{
		$this->putInCustomData(self::CUSTOM_DATA_PRICING, serialize($pricing));
	}
	
	/**
	 * @return vCatalogItemPricing
	 */
	public function getPricing()
	{
		$pricing = $this->getFromCustomData(self::CUSTOM_DATA_PRICING);
		
		if($pricing)
			$pricing = unserialize($pricing);
		
		return $pricing;
	}
	
	public function getPartnerId()
	{
		return 0;
	}
	
	public function getVsExpiry()
	{
		$vsExpiry = $this->getTurnAroundTime() * 2;
		
		//Minimum VS expiry should be set 7 days
		return max($vsExpiry, dateUtils::DAY * 7);
	}
	
	public function calculatePriceForEntry(entry $entry)
	{
		return call_user_func($this->getPricing()->getPriceFunction(), $entry, $this->getPricing()->getPricePerUnit());
	}
	
	public function getTaskVersion($entryId, $jobData = null)
	{
		$sourceFlavor = assetPeer::retrieveOriginalByEntryId($entryId);
		return $sourceFlavor != null ? $sourceFlavor->getVersion() : 0;
	}

} // VendorCatalogItem
