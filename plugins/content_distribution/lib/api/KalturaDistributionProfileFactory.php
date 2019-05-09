<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api
 */
class VidiunDistributionProfileFactory
{	
	/**
	 * @param int $providerType
	 * @return VidiunDistributionProfile
	 */
	public static function createVidiunDistributionProfile($providerType)
	{
		if($providerType == VidiunDistributionProviderType::GENERIC)
			return new VidiunGenericDistributionProfile();
			
		if($providerType == VidiunDistributionProviderType::SYNDICATION)
			return new VidiunSyndicationDistributionProfile();
			
		$distributionProfile = VidiunPluginManager::loadObject('VidiunDistributionProfile', $providerType);
		if($distributionProfile)
			return $distributionProfile;
		
		return null;
	}
}