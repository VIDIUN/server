<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.objects
 */
class VidiunSystemPartnerPackageArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunSystemPartnerPackage");
	}
	
	public function fromArray($arr)
	{
		foreach($arr as $item)
		{
			$obj = new VidiunSystemPartnerPackage();
			$obj->fromArray($item);
			$this[] = $obj;
		}
	}
}