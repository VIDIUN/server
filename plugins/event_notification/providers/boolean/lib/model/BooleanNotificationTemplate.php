<?php
/**
 * @package plugins.booleanNotification
 * @subpackage model
 */
class BooleanNotificationTemplate extends BatchEventNotificationTemplate //implements ISyncableFile
{
	public function __construct()
	{
		$this->setType(BooleanNotificationPlugin::getBooleanNotificationTemplateTypeCoreValue(BooleanNotificationTemplateType::BOOLEAN));
		parent::__construct();
	}

	/* (non-PHPdoc)
	* @see BatchEventNotificationTemplate::getJobData()
	*/
	protected function getJobData(vScope $scope = null)
	{
		$jobData = new vEventNotificationDispatchJobData();
		$jobData->setTemplateId($this->getId());
		return $jobData;
	}
}