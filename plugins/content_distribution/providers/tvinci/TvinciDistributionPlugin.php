<?php
/**
 * @package plugins.tvinciDistribution
 */
class TvinciDistributionPlugin extends VidiunParentContributedPlugin implements IVidiunPermissions, IVidiunEnumerator, IVidiunPending, IVidiunObjectLoader, IVidiunContentDistributionProvider
{
	const PLUGIN_NAME = 'tvinciDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 1;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	public static function dependsOn()
	{
		$contentDistributionVersion = new VidiunVersion(
			self::CONTENT_DSTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DSTRIBUTION_VERSION_MINOR,
			self::CONTENT_DSTRIBUTION_VERSION_BUILD);

		$dependency = new VidiunDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		return array($dependency);
	}

	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;

		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(ContentDistributionPlugin::getPluginName());
	}

	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('TvinciDistributionProviderType', 'ParentObjectFeatureType');

		if($baseEnumName == 'ObjectFeatureType')
			return array('ParentObjectFeatureType');

		if($baseEnumName == 'DistributionProviderType')
			return array('TvinciDistributionProviderType');

		return array();
	}

	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		// client side apps like batch and admin console
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::TVINCI)
		{
			if($baseClass == 'IDistributionEngineDelete')
				return new TvinciDistributionFeedEngine();

			if($baseClass == 'IDistributionEngineReport')
				return new TvinciDistributionFeedEngine();

			if($baseClass == 'IDistributionEngineSubmit')
				return new TvinciDistributionFeedEngine();

			if($baseClass == 'IDistributionEngineUpdate')
				return new TvinciDistributionFeedEngine();

			if($baseClass == 'VidiunDistributionProfile')
				return new VidiunTvinciDistributionProfile();

			if($baseClass == 'VidiunDistributionJobProviderData')
				return new VidiunTvinciDistributionJobProviderData();
		}

		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::TVINCI)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_TvinciProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}

		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
		{
			$reflect = new ReflectionClass('VidiunTvinciDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}

		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(TvinciDistributionProviderType::TVINCI))
		{
			$reflect = new ReflectionClass('vTvinciDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}

		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
			return new VidiunTvinciDistributionProfile();

		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
			return new TvinciDistributionProfile();

		return null;
	}

	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// client side apps like batch and admin console
		if (class_exists('VidiunClient') && $enumValue == VidiunDistributionProviderType::TVINCI)
		{
			if($baseClass == 'IDistributionEngineDelete')
				return 'TvinciDistributionFeedEngine';

			if($baseClass == 'IDistributionEngineReport')
				return 'TvinciDistributionFeedEngine';

			if($baseClass == 'IDistributionEngineSubmit')
				return 'TvinciDistributionFeedEngine';

			if($baseClass == 'IDistributionEngineUpdate')
				return 'TvinciDistributionFeedEngine';

			if($baseClass == 'VidiunDistributionProfile')
				return 'VidiunTvinciDistributionProfile';

			if($baseClass == 'VidiunDistributionJobProviderData')
				return 'VidiunTvinciDistributionJobProviderData';
		}

		if (class_exists('Vidiun_Client_Client') && $enumValue == Vidiun_Client_ContentDistribution_Enum_DistributionProviderType::TVINCI)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_TvinciProfileConfiguration';

			if($baseClass == 'Vidiun_Client_ContentDistribution_Type_DistributionProfile')
				return 'Vidiun_Client_TvinciDistribution_Type_TvinciDistributionProfile';
		}

		if($baseClass == 'VidiunDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
			return 'VidiunTvinciDistributionJobProviderData';

		if($baseClass == 'vDistributionJobProviderData' && $enumValue == self::getApiValue(TvinciDistributionProviderType::TVINCI))
			return 'vTvinciDistributionJobProviderData';

		if($baseClass == 'VidiunDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
			return 'VidiunTvinciDistributionProfile';

		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(TvinciDistributionProviderType::TVINCI))
			return 'TvinciDistributionProfile';

		return null;
	}

	/**
	 * Return a distribution provider instance
	 *
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return TvinciDistributionProvider::get();
	}

	/**
	 * Return an API distribution provider instance
	 *
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider()
	{
		$distributionProvider = new VidiunTvinciDistributionProvider();
		$distributionProvider->fromObject(self::getProvider());
		return $distributionProvider;
	}

	/**
	 * Append provider specific nodes and attributes to the MRSS
	 *
	 * @param EntryDistribution $entryDistribution
	 * @param SimpleXMLElement $mrss
	 */
	public static function contributeMRSS(EntryDistribution $entryDistribution, SimpleXMLElement $mrss)
	{

	}


	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDistributionProviderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DistributionProviderType', $value);
	}

	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}
