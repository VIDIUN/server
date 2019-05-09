<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunDeleteLocalContentObjectTask extends VidiunObjectTask
{
	public function __construct()
	{
		$this->type = ObjectTaskType::DELETE_LOCAL_CONTENT;
	}
}