<?php

if ($argc < 3)
	die("Usage:\n\tphp syncInvalidSessionsToMemcache <keys memcache host> <keys memcache port>\n");

$MC_HOST_NAME = $argv[1];
$MC_PORT = 	 	$argv[2];

define('EXPIRY_TIME_MARGIN', 600);
define('PAGE_SIZE', 1000);

require_once(dirname(__FILE__).'/../bootstrap.php');

myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

$lastID = null;

$memcache = new Memcache;	
$res = @$memcache->connect($MC_HOST_NAME, $MC_PORT);
if (!$res)
	die('Error: failed to connect to global memcache !');

$setCount = 0;

for (;;)
{
	$c = new Criteria();
	if ($lastID !== null)
		$c->add(invalidSessionPeer::ID, $lastID, Criteria::GREATER_THAN);
	$c->addAscendingOrderByColumn(invalidSessionPeer::ID);
	$c->setLimit(PAGE_SIZE);
	$results = invalidSessionPeer::doSelect($c);
	if (!count($results))
		break;

	foreach ($results as $result)
	{
		$lastID = $result->getId();

		$vsKey = vSessionBase::INVALID_SESSION_KEY_PREFIX . $result->getVs();
		$vsValidUntil = $result->getVsValidUntil(null);
		$keyExpiry = 0;			// non expiring
		if ($vsValidUntil !== null)
		{
			if ($vsValidUntil + EXPIRY_TIME_MARGIN < time())
				continue;		// already expired
			$keyExpiry = $vsValidUntil + EXPIRY_TIME_MARGIN;
		}
		if ($memcache->set($vsKey, true, 0, $keyExpiry) === false)
			die("Error: failed to set key [{$vsKey}] with expiry [{$keyExpiry}]");
			
		$setCount++;
	}
}

if ($memcache->set(vSessionBase::INVALID_SESSIONS_SYNCED_KEY, true) === false)
	die("Error: failed to set key [" . vSessionBase::INVALID_SESSIONS_SYNCED_KEY . "]");

print("Done!\n{$setCount} keys set\n");
