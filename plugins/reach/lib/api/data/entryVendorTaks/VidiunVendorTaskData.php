<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 * @relatedService EntryVendorTaskService
 */
abstract class VidiunVendorTaskData extends VidiunObject implements IApiObjectFactory
{
	/* (non-PHPdoc)
 	 * @see IApiObjectFactory::getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile)
 	 */
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$taskDataType = get_class($sourceObject);
		$taskData = null;
		switch ($taskDataType)
		{
			case 'vIdignmentVendorTaskData':
				$taskData = new VidiunAlignmentVendorTaskData();
				break;
		}
		
		if ($taskData)
			/* @var $object VidiunVendorTaskData */
			$taskData->fromObject($sourceObject, $responseProfile);
		
		return $taskData;
	}
}
