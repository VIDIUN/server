<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunContextTypeHolderArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunContextTypeHolderArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $type)
		{
			$nObj = self::getInstanceByType($type);				
			$nObj->type = $type;
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	static function getInstanceByType($type)
	{
		switch($type)
		{
			case ContextType::DOWNLOAD:
			case ContextType::PLAY:
			case ContextType::THUMBNAIL:
			case ContextType::METADATA:
				return new VidiunAccessControlContextTypeHolder();
			default:
				return new VidiunContextTypeHolder();
		}		
	}
	
	public function __construct()
	{
		parent::__construct("VidiunContextTypeHolder");	
	}
}