<?php
$config = null;
$clientConfig = null;
/* @var $clientConfig VidiunConfiguration */
$client = null;
/* @var $client VidiunClient */

require_once __DIR__ . '/lib/init.php';
echo "Test started [" . __FILE__ . "]\n";


/**
 * Start a new session
 */
$adminSecretForSigning = $config['adminConsoleSession']['adminSecret'];
$client->setVs($client->generateSessionV2($adminSecretForSigning, null, VidiunSessionType::ADMIN, -2, 86400, ''));
echo "Admin console session started\n";


$partnerId = $config['session']['partnerId'];

/**
 * Delete the partner
 */
$systemPartnerClient = VidiunSystemPartnerClientPlugin::get($client);
$systemPartnerClient->systemPartner->updateStatus($partnerId, VidiunPartnerStatus::FULL_BLOCK, "Test Delete Partner");
echo "Partner [$partnerId] deleted\n";

/**
 * All is SABABA
 */
echo "OK\n";
exit(0);
