<?php
/**
 * @package plugins.httpNotification
 * @subpackage api.objects
 * @abstract
 */
abstract class VidiunHttpNotificationData extends VidiunObject
{
	/**
	 * @param vHttpNotificationData $coreObject
	 * @return VidiunHttpNotificationData
	 */
	public static function getInstance(vHttpNotificationData $coreObject)
	{
		$dataType = get_class($coreObject);
		$data = null;
		switch ($dataType)
		{
			case 'vHttpNotificationDataFields':
				$data = new VidiunHttpNotificationDataFields();
				break;
				
			case 'vHttpNotificationDataText':
				$data = new VidiunHttpNotificationDataText();
				break;
				
			case 'vHttpNotificationObjectData':
				$data = new VidiunHttpNotificationObjectData();
				break;
				
			default:
				$data = VidiunPluginManager::loadObject('VidiunHttpNotificationData', $dataType);
				break;
		}
		
		if($data)
			$data->fromObject($coreObject);
			
		return $data;
	}

	/**
	 * @param $jobData vHttpNotificationDispatchJobData
	 * @return string the data to be sent
	 */
	abstract public function getData(vHttpNotificationDispatchJobData $jobData = null);
}
