<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunSearchItemArray extends VidiunTypedArray
{
	/**
	 * @param array $arr
	 * @return VidiunSearchItemArray
	 */
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunSearchItemArray();
		if(!$arr || !count($arr))
			return $newArr;
			
		foreach ( $arr as $obj )
		{
			$vidiunClass = $obj->getVidiunClass();
			if(!class_exists($vidiunClass))
			{
				VidiunLog::err("Class [$vidiunClass] not found");
				continue;
			}
				
			$nObj = new $vidiunClass();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	/**
	 * @return array
	 */
	public function toObjectsArray()
	{
		$ret = array();
		foreach($this as $item)
		{
			$ret[] = $item->toObject();
		}
			
		return $ret;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunSearchItem" );
	}
}
?>