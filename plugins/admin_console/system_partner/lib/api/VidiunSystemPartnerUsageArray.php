<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.objects
 */
class VidiunSystemPartnerUsageArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunSystemPartnerUsageItem");
	}
}