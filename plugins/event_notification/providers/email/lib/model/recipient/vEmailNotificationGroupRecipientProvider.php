<?php
/**
 * Core class for recipient provider which provides a dynamic list of user recipients based on filter.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationGroupRecipientProvider extends vEmailNotificationRecipientProvider
{
	/**
	 * @var string
	 */
	protected $groupId;
	
	/**
	 * @return string $groupId
	 */
	public function getGroupId() {
		return $this->groupId;
	}

	/**
	 * @param string $groupId
	 */
	public function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
	
	/* (non-PHPdoc)
	 * @see vEmailNotificationRecipientProvider::getScopedProviderJobData()
	 */
	public function getScopedProviderJobData(vScope $scope = null) 
	{
		$ret = new vEmailNotificationGroupRecipientJobData();
		$ret->setGroupId($this->groupId);
		return $ret;
	}
	


	
}