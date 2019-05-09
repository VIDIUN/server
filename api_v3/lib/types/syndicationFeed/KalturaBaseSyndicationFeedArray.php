<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBaseSyndicationFeedArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunBaseSyndicationFeedArray();
		if ( $arr == null ) return $newArr;
		foreach ( $arr as $obj )
		{
			$nObj = VidiunSyndicationFeedFactory::getInstanceByType($obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunBaseSyndicationFeed");	
	}
}