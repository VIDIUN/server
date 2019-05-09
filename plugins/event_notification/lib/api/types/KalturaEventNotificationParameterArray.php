<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventNotificationParameterArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunEventNotificationParameterArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$parameterType = get_class($obj);
			switch ($parameterType)
			{
				case 'vEventNotificationParameter':
    				$nObj = new VidiunEventNotificationParameter();
					break;
					
				case 'vEventNotificationArrayParameter':
    				$nObj = new VidiunEventNotificationArrayParameter();
					break;
					
				default:
    				$nObj = VidiunPluginManager::loadObject('VidiunEventNotificationParameter', $parameterType);
			}
			
			if($nObj)
			{
				$nObj->fromObject($obj, $responseProfile);
				$newArr[] = $nObj;
			}
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunEventNotificationParameter");	
	}
}