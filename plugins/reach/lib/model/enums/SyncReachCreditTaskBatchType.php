<?php

/**
 * @package plugins.reach
 * @subpackage model.enum
 */
class SyncReachCreditTaskBatchType implements IVidiunPluginEnum, BatchJobType
{
	const SYNC_REACH_CREDIT_TASK = 'SyncReachCreditTask';

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'SYNC_REACH_CREDIT_TASK' => self::SYNC_REACH_CREDIT_TASK,
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
