<?php

chdir(dirname(__FILE__));
require_once(dirname(__FILE__) . '/../bootstrap.php');

$partnerId = null;
$partnerPackage = 1;
if($argc > 1 && is_numeric($argv[1]))
	$partnerId = $argv[1];
if($argc > 2 && is_numeric($argv[2]))
	$partnerPackage = $argv[2];
	
if(in_array('dryRun', $argv))
	VidiunStatement::setDryRun(true);
	
vCurrentContext::$master_partner_id = -2;
vCurrentContext::$uid = "PARTNER USAGE DAEMON";

// Make sure that events will be performed immediately (e.g. creating a new vuser for the given puser)
vEventsManager::enableDeferredEvents(false);

$batchClient = new myBatchPartnerUsage($partnerId, $partnerPackage);

VidiunLog::debug('Done.');
