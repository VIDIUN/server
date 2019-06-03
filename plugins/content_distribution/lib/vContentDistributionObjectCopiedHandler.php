<?php
class vContentDistributionObjectCopiedHandler implements vObjectCopiedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectCopiedEventConsumer::shouldConsumeCopiedEvent()
	 */
	public function shouldConsumeCopiedEvent(BaseObject $fromObject, BaseObject $toObject)
	{
		if($fromObject instanceof Partner)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectCopiedEventConsumer::objectCopied()
	 */
	public function objectCopied(BaseObject $fromObject, BaseObject $toObject)
	{
		$this->copyDistributionProfiles($fromObject->getId(), $toObject->getId());
		
		return true;
	}
	
	/**
	 * @param int $fromPartnerId
	 * @param int $toPartnerId
	 */
	protected function copyDistributionProfiles($fromPartnerId, $toPartnerId)
	{
 		$c = new Criteria();
 		$c->add(DistributionProfilePeer::PARTNER_ID, $fromPartnerId);
 		
 		$distributionProfiles = DistributionProfilePeer::doSelect($c);
 		foreach($distributionProfiles as $distributionProfile)
 		{
 			$newDistributionProfile = $distributionProfile->copy();
 			$newDistributionProfile->setPartnerId($toPartnerId);
 			$newDistributionProfile->save();
 			
 			vFileSyncUtils::createSyncFileLinkForKey(
 				$newDistributionProfile->getSyncKey(DistributionProfile::FILE_SYNC_DISTRIBUTION_PROFILE_CONFIG),
 				$distributionProfile->getSyncKey(DistributionProfile::FILE_SYNC_DISTRIBUTION_PROFILE_CONFIG)
 			);
 		}
	}
}