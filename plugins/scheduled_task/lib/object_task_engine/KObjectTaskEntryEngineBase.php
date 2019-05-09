<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
abstract class VObjectTaskEntryEngineBase extends VObjectTaskEngineBase
{
	function getSupportedObjectTypes()
	{
		return array('VidiunBaseEntry');
	}
} 