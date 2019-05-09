<?php

/**
 * @package plugins.reach
 * @subpackage model.enum
 */
class ReachEntryVendorTasksCsvBatchType implements IVidiunPluginEnum, BatchJobType
{
	const ENTRY_VENDOR_TASK_CSV = 'EntryVendorTasksCsv';

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'ENTRY_VENDOR_TASK_CSV' => self::ENTRY_VENDOR_TASK_CSV,
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
