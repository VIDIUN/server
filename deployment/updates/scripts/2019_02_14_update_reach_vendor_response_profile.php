<?php
/**
 * @package deployment
 */
require_once (__DIR__ . '/../../bootstrap.php');

$script = realpath(dirname(__FILE__) . '/../../../') . '/tests/standAloneClient/exec.php';
$config = realpath(dirname(__FILE__)) . '/xml/responseProfiles/2019_02_14_update_reach_vendor_response_profiles.xml';

if(!file_exists($config))
{
	VidiunLog::err("Missing update script file");
	return;
}

passthru("php $script $config");
