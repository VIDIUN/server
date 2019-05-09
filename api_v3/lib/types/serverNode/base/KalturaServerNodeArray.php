<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunServerNodeArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunServerNodeArray();
		foreach($arr as $obj)
		{
			$nObj = VidiunServerNode::getInstance($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunServerNode" );
	}
}