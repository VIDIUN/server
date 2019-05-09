<?php

require_once(dirname(__FILE__) . '/../../bootstrap.php');

myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_MASTER;

vPluginableEnumsManager::enableNewValues();

$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunEnumerator');
foreach($pluginInstances as $pluginInstance)
{
	$pluginName = $pluginInstance->getPluginName();
	VidiunLog::debug("Installs plugin [$pluginName]");
	$enums = $pluginInstance->getEnums();
	foreach($enums as $enum)
	{
		$interfaces = class_implements($enum);
		foreach($interfaces as $interface)
		{
			if($interface == 'IVidiunPluginEnum' || $interface == 'BaseEnum')
				continue;
		
			$interfaceInterfaces = class_implements($interface);
			if(!in_array('BaseEnum', $interfaceInterfaces))
				continue;
				
			VidiunLog::debug("Installs enum [$enum] of type [$interface]");
			$values = call_user_func(array($enum, 'getAdditionalValues'));
			foreach($values as $value)
			{
				$enumValue = $pluginName . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $value;
				VidiunLog::debug("Installs enum value [$enumValue] to type [$interface]");
				vPluginableEnumsManager::apiToCore($interface, $enumValue);
			}
		}
	}
}

