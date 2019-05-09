<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 * @abstract
 */

abstract class VidiunBaseVendorCredit extends VidiunObject implements IApiObjectFactory
{
	/* (non-PHPdoc)
 	 * @see IApiObjectFactory::getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile)
 	 */
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$creditType = get_class($sourceObject);
		$credit = null;
		switch ($creditType)
		{
			case 'vVendorCredit':
				$credit = new VidiunVendorCredit();
				break;

			case 'vTimeRangeVendorCredit':
				$credit = new VidiunTimeRangeVendorCredit();
				break;

			case 'vReoccurringVendorCredit':
				$credit = new VidiunReoccurringVendorCredit();
				break;

			case 'vUnlimitedVendorCredit':
				$credit = new VidiunUnlimitedVendorCredit();
				break;
		}

		if ($credit)
			/* @var $object VidiunBaseVendorCredit */
			$credit->fromObject($sourceObject, $responseProfile);

		return $credit;
	}

		/* (non-PHPdoc)
		* @see VidiunObject::validateForInsert()
		*/
	public function validateForInsert($propertiesToSkip = array())
  	{
		parent::validateForInsert($propertiesToSkip);
	}
	
	public function hasObjectChanged($sourceObject)
	{
		return false;
	}
}
