<?php
/**
 * @package api
 * @subpackage enum
 */
abstract class VidiunDynamicEnum extends VidiunStringEnum implements IVidiunDynamicEnum
{
	public static function mergeDescriptions($baseEnumName, array $descriptions)
	{
		$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunEnumerator');
		foreach($pluginInstances as $pluginInstance)
		{
			$pluginName = $pluginInstance->getPluginName();
			$enums = $pluginInstance->getEnums($baseEnumName);
			foreach($enums as $enum)
			{
				$additionalDescriptions = $enum::getAdditionalDescriptions();
				foreach($additionalDescriptions as $key => $description)
					$descriptions[$key] = $description;
			}
		}
		return $descriptions;
	}
}
