<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunServerNodeStatus extends VidiunEnum implements ServerNodeStatus
{
	public static function getEnumClass()
	{
		return 'ServerNodeStatus';
	}
}