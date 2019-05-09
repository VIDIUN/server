<?php
chdir(dirname(__FILE__));

require_once(__DIR__ . '/../../bootstrap.php');


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
$c->setLimit(10000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);
//$sphinxCon = DbManager::getSphinxConnection();

$entries = vuserPeer::doSelect($c, $con);
$sphinx = new vSphinxSearchManager();
while(count($entries))
{
	foreach($entries as $entry)
	{
	    /* @var $entry vuser */
		VidiunLog::log('vuser id ' . $entry->getId() . ' updated at '. $entry->getUpdatedAt(null));
		
		try {
			$ret = $sphinx->saveToSphinx($entry, true);
		}
		catch(Exception $e){
			VidiunLog::err($e->getMessage());
			exit -1;
		}
	}
	
	$c->setOffset($c->getOffset() + count($entries));
	vMemoryManager::clearMemory();
	$entries = vuserPeer::doSelect($c, $con);
}

VidiunLog::log('Done. Cureent time: ' . time());
exit(0);
