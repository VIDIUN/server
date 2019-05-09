<?php
chdir(dirname(__FILE__));

require_once(__DIR__ . '/../../../bootstrap.php');


$c = new Criteria();

if($argc > 1 && is_numeric($argv[1]))
	$c->add(vuserPeer::UPDATED_AT, $argv[1], Criteria::GREATER_EQUAL);
if($argc > 2 && is_numeric($argv[2]))
	$c->add(vuserPeer::PARTNER_ID, $argv[2], Criteria::EQUAL);
if($argc > 3 && is_numeric($argv[3]))
	$c->add(vuserPeer::ID, $argv[3], Criteria::GREATER_EQUAL);
if($argc > 4)
	vuserPeer::setUseCriteriaFilter((bool)$argv[4]);

$c->addAscendingOrderByColumn(vuserPeer::UPDATED_AT);
$c->addAscendingOrderByColumn(vuserPeer::ID);
$c->setLimit(1000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

$vusers = vuserPeer::doSelect($c, $con);
$elasticManager = new vElasticSearchManager();
while(count($vusers))
{
	foreach($vusers as $vuser)
	{
		VidiunLog::log('vuser id ' . $vuser->getId() . ' updated at '. $vuser->getUpdatedAt(null));

		try 
		{
			$elasticManager->saveToElastic($vuser);
		}
		catch(Exception $e){
			VidiunLog::err($e->getMessage());
			exit -1;
		}
	}

	$c->setOffset($c->getOffset() + count($vusers));
	vMemoryManager::clearMemory();
	$vusers = vuserPeer::doSelect($c, $con);
}

VidiunLog::log('Done. Current time: ' . time());
exit(0);
