<?php

/**
 * Subclass for representing a row from the 'system_user' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class SystemUser extends BaseSystemUser

//TODO: class is deprecated - should be deleted after users migration!
{
	const SYSTEM_USER_BLOCKED = 0;
	const SYSTEM_USER_ACTIVE = 1;
	
	public function getName()
	{
		return ($this->getFirstName() . ' ' . $this->getLastName());
	}
	
	private function generateSalt()
	{
		return md5(rand(100000, 999999));
	}
	
	public function setPassword($password) 
	{ 
		$this->setSalt($this->generateSalt()); 
		$this->setSha1Password(sha1($this->getSalt().$password));  
	} 
	
	public function isPasswordValid($passwordToMatch)
	{
		return sha1($this->getSalt().$passwordToMatch) === $this->getSha1Password();
	}
	
	public function save(PropelPDO $con = null)
	{
		if ($this->isNew())
		{
			// password was not set, generate a new one
			if (!$this->isColumnModified(SystemUserPeer::SHA1_PASSWORD))
			{
				$password = self::generateRandomPassword();
				$this->setPassword($password);
				$this->sendEmail($password);
			}
		}
		
		if ($this->isColumnModified(SystemUserPeer::STATUS))
			$this->setStatusUpdatedAt(time());
			
		parent::save($con);
	}
	
	public static function generateRandomPassword()
	{
		return vString::generateRandomString(5, 10, true, false, true);
	} 
	
	private function sendEmail($password)
	{
		$batchJob = new BatchJob();
		$batchJob->setPartnerId(Partner::ADMIN_CONSOLE_PARTNER_ID);
		
		$jobData = new vMailJobData();
		$jobData->setMailPriority(vMailJobData::MAIL_PRIORITY_NORMAL);
		$jobData->setStatus(vMailJobData::MAIL_STATUS_PENDING);
		
		$jobData->setBodyParamsArray(array($password));
		$jobData->setMailType(112);
		
		$jobData->setFromEmail(vConf::get("default_email"));
		$jobData->setFromName(vConf::get("default_email_name"));
		$jobData->setRecipientEmail($this->getEmail());
		$jobData->setSubjectParamsArray(array());
		
		vJobsManager::addJob($batchJob, $jobData, BatchJobType::MAIL, $jobData->getMailType());
	}
}
