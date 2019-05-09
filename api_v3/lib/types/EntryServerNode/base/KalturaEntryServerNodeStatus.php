<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunEntryServerNodeStatus extends VidiunEnum implements EntryServerNodeStatus{

	public static function getEnumClass()
	{
		return 'EntryServerNodeStatus';
	}
}