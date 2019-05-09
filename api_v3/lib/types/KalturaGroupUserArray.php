<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunGroupUserArray extends VidiunTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new VidiunGroupUserArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunGroupUser();
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunGroupUser");
	}
}