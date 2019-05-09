<?php
/**
 * Core class for recipient provider containing a static list of email recipients.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class vEmailNotificationStaticRecipientProvider extends vEmailNotificationRecipientProvider
{
	/**
	 * Email notification "to" sendees
	 * @var array
	 */
	protected $emailRecipients;

	/**
	 * @return array
	 */
	public function getEmailRecipients() {
		return $this->emailRecipients;
	}

	/**
	 * @param array $to
	 */
	public function setEmailRecipients($v) {
		$this->emailRecipients = $v;
	}
	

	/* (non-PHPdoc)
	 * @see vEmailNotificationRecipientProvider::getScopedProviderJobData()
	 */
	public function getScopedProviderJobData(vScope $scope = null) 
	{
		$implicitEmailRecipients = array();
		foreach($this->emailRecipients as &$emailRecipient)
		{
			/* @var $emailRecipient vEmailNotificationRecipient */
			$email = $emailRecipient->getEmail();
			if($scope && $email instanceof vStringField)
				$email->setScope($scope);

			$name = $emailRecipient->getName();
			if($scope && $name instanceof vStringField)
				$name->setScope($scope);
			$theName = "";
            if ($name)
			    $theName = $name->getValue();
			    			
			$implicitEmailRecipients[$email->getValue()] = $theName;
		}
		
		$ret = new vEmailNotificationStaticRecipientJobData();
		$ret->setEmailRecipients($implicitEmailRecipients);
		
		return $ret;
		
	}
}