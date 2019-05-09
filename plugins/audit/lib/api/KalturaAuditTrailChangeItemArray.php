<?php
/**
 * @package plugins.audit
 * @subpackage api.objects
 */
class VidiunAuditTrailChangeItemArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAuditTrailChangeItemArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunAuditTrailChangeItem();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunAuditTrailChangeItem");	
	}
	
	public function toObjectArray()
	{
		$ret = array();
		
		foreach($this as $item)
			$ret[] = $item->toObject();
			
		return $ret;
	}
}
