<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects
 */
class VidiunObjectTaskArray extends VidiunTypedArray
{
	public function __construct()
	{
		parent::__construct('VidiunObjectTask');
	}

	public static function fromDbArray(array $dbArray, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$apiArray = new VidiunObjectTaskArray();
		foreach($dbArray as $dbObject)
		{
			/** @var vObjectTask $dbObject */
			$apiObject = VidiunObjectTask::getInstanceByDbObject($dbObject);
			if (is_null($apiObject))
			{
				throw new Exception('Couldn\'t load api object for db object '.$dbObject->getType());
			}
			$apiObject->fromObject($dbObject, $responseProfile);;
			$apiArray[] = $apiObject;
		}

		return $apiArray;
	}
}