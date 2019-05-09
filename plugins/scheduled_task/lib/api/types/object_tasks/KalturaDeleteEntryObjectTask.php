<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunDeleteEntryObjectTask extends VidiunObjectTask
{
	public function __construct()
	{
		$this->type = ObjectTaskType::DELETE_ENTRY;
	}
}