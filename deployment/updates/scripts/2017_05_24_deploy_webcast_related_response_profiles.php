<?php
/**
 * @package deployment
 *
 * Deploy webcast defualt profiles & temlates
 *
 * No need to re-run after server code deploy
 */
require_once (__DIR__ . '/../../bootstrap.php');
$script = realpath(dirname(__FILE__) . '/../../../') . '/tests/standAloneClient/exec.php';

$config = realpath(dirname(__FILE__)) . '/xml/responseProfiles/polls_response_profile.xml';
if(!file_exists($config))
	VidiunLog::err("Missing file [$config] will not deploy");
passthru("php $script $config");

$config = realpath(dirname(__FILE__)) . '/xml/responseProfiles/qna_response_profiles.xml';
if(!file_exists($config))
	VidiunLog::err("Missing file [$config] will not deploy");
passthru("php $script $config");

