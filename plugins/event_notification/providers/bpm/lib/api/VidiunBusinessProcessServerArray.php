<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.objects
 */
class VidiunBusinessProcessServerArray extends VidiunTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new VidiunBusinessProcessServerArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			/* @var $obj BusinessProcessServer */
    		$nObj = VidiunBusinessProcessServer::getInstanceByType($obj->getType());
    		if(!$nObj)
    		{
    			VidiunLog::err("Business-Process server could not find matching type for [" . $obj->getType() . "]");
    			continue;
    		}
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunBusinessProcessServer");	
	}
}