<?php
/**
 * @package deployment
 */
require_once (__DIR__ . '/../../bootstrap.php');

$script = realpath(dirname(__FILE__) . "/../../../tests/standAloneClient/exec.php");

$newTemplateUpdate = realpath(dirname(__FILE__) . "/../../updates/scripts/xml/notifications/2018_05_27_player_slide_module_notifications.xml");

if(!file_exists($newTemplateUpdate) || !file_exists($script))
{
	VidiunLog::err("Missing update script file");
	return;
}

passthru("php $script $newTemplateUpdate");
