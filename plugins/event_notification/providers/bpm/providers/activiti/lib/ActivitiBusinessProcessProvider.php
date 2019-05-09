<?php
/**
 * @package plugins.activitiBusinessProcessNotification
 * @subpackage model.enum
 */
class ActivitiBusinessProcessProvider implements IVidiunPluginEnum, BusinessProcessProvider
{
	const ACTIVITI = 'Activiti';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'ACTIVITI' => self::ACTIVITI,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array(
			self::ACTIVITI => 'Activiti BPM Platform',
		);
	}
}
