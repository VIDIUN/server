<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectFilterEngine
 */
class VObjectFilterEntryVendorTaskEngine extends VObjectFilterEngineBase
{
	/**
	 * @param VidiunFilter $filter
	 * @return array
	 */
	public function query(VidiunFilter $filter)
	{
		return $this->_client->entryVendorTask->listAction($filter, $this->getPager());
	}
}