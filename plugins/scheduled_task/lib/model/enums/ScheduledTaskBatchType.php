<?php
/**
 * @package plugins.scheduledTask
 * @subpackage model.enum
 */ 
class ScheduledTaskBatchType implements IVidiunPluginEnum, BatchJobType
{
	const SCHEDULED_TASK = 'ScheduledTask';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'SCHEDULED_TASK' => self::SCHEDULED_TASK,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array();
	}
}
