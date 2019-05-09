<?php

chdir(__DIR__ . '/../');
require_once(__DIR__ . '/../bootstrap.php');

// add google authenticator library to include path
require_once VIDIUN_ROOT_PATH . '/vendor/phpGangsta/GoogleAuthenticator.php';

$criteria = VidiunCriteria::create(vuserPeer::OM_CLASS);
$criteria->add(vuserPeer::PARTNER_ID, -2);

$vusers = vuserPeer::doSelect($criteria);
foreach ($vusers as $vuser)
{
	/*@var $vuser vuser */
	$userLoginData = $vuser->getLoginData();
	if (!$userLoginData)
		continue;
	
	VidiunLog::info ("setting user hash for user: " . $vuser->getPuserId());
	$userLoginData->setSeedFor2FactorAuth(GoogleAuthenticator::createSecret());
	$userLoginData->save();
	
}
