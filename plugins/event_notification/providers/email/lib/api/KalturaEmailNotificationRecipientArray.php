<?php
/**
 * @package plugins.emailNotification
 * @subpackage api.objects
 */
class VidiunEmailNotificationRecipientArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunEmailNotificationRecipientArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunEmailNotificationRecipient();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunEmailNotificationRecipient");	
	}
}