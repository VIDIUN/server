<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.objects
 */
class VidiunBusinessProcessCaseArray extends VidiunTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new VidiunBusinessProcessCaseArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			/* @var $obj vBusinessProcessCase */
    		$nObj = new VidiunBusinessProcessCase();
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunBusinessProcessCase");	
	}
}