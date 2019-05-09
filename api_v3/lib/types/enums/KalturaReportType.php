<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunReportType extends VidiunDynamicEnum implements ReportType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'ReportType';
	}

}
