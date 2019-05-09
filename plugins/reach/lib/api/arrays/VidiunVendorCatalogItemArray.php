<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */
class VidiunVendorCatalogItemArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunVendorCatalogItemArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$newArr[] = VidiunVendorCatalogItem::getInstance($obj, $responseProfile);
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunVendorCatalogItem");	
	}
}