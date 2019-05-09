<?php
/**
 * @package plugins.viewHistory
 * @subpackage model.enum
 */
class ViewHistoryUserEntryType implements IVidiunPluginEnum, UserEntryType
{
	const VIEW_HISTORY = "VIEW_HISTORY";
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			"VIEW_HISTORY" => self::VIEW_HISTORY,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions()
	{
		return array(
			self::VIEW_HISTORY => 'View History User Entry Type',
		);
	}
}