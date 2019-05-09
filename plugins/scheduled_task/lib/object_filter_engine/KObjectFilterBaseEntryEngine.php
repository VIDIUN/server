<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectFilterEngine
 */
class VObjectFilterBaseEntryEngine extends VObjectFilterEngineBase
{
	/**
	 * @param VidiunFilter $filter
	 * @return array
	 */
	public function query(VidiunFilter $filter)
	{
		return $this->_client->baseEntry->listAction($filter, $this->getPager());
	}
}