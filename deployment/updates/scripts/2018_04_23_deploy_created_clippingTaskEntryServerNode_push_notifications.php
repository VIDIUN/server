<?php

require_once(__DIR__ . "/../../../alpha/scripts/bootstrap.php");

checkMandatoryPluginsEnabled();
deployLiveClippingPushNotifications();

function deployLiveClippingPushNotifications()
{
	$script = realpath(dirname(__FILE__) . "/../../../tests/standAloneClient/exec.php");

	$clippingTaskEntryServerNodeCreationTemplate = realpath(dirname(__FILE__) . "/../../updates/scripts/xml/notifications/clippingTaskEntryServerNode_created_notification.xml");

	if (!file_exists($clippingTaskEntryServerNodeCreationTemplate))
	{
		VidiunLog::err("Missing notification file for deployign notifications");
		return;
	}

	passthru("php $script $clippingTaskEntryServerNodeCreationTemplate");
}

/**
 * Check if all plugins needed for entryServerNode created to work are installed
 * @return bool If all required plugins are installed
 */
function checkMandatoryPluginsEnabled()
{
	$requiredPlugins = array("PushNotification", "Queue", "RabbitMQ");
	$pluginsFilePath = realpath(dirname(__FILE__) . "/../../../configurations/plugins.ini");
	VidiunLog::debug("Loading Plugins config from [$pluginsFilePath]");
	
	$pluginsData = file_get_contents($pluginsFilePath);
	foreach ($requiredPlugins as $requiredPlugin)
	{
		//check if plugin exists in file but is disabled
		if(strpos($pluginsData, ";".$requiredPlugin) !== false)
		{
			VidiunLog::debug("[$requiredPlugin] is disabled, aborting execution");
			exit(-2);
		}
		
		if(strpos($pluginsData, $requiredPlugin) === false)
		{
			VidiunLog::debug("[$requiredPlugin] not found in plugins data, aborting execution");
			exit(-2);
		}
	}
}
