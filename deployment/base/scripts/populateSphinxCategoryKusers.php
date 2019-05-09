<?php
chdir(dirname(__FILE__));

require_once(__DIR__ . '/../../bootstrap.php');


$c = new Criteria();

if($argc > 1 && is_numeric($argv[1]))
	$c->add(categoryVuserPeer::UPDATED_AT, $argv[1], Criteria::GREATER_EQUAL);
if($argc > 2 && is_numeric($argv[2]))
	$c->add(categoryVuserPeer::PARTNER_ID, $argv[2], Criteria::EQUAL);
if($argc > 3 && is_numeric($argv[3]))
	$c->add(categoryVuserPeer::ID, $argv[3], Criteria::GREATER_EQUAL);
if($argc > 4)
	categoryVuserPeer::setUseCriteriaFilter((bool)$argv[4]);

$c->addAscendingOrderByColumn(categoryVuserPeer::UPDATED_AT);
$c->addAscendingOrderByColumn(categoryVuserPeer::ID);
$c->setLimit(10000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);
//$sphinxCon = DbManager::getSphinxConnection();
categoryVuserPeer::setUseCriteriaFilter(false);
$categoryVusers = categoryVuserPeer::doSelect($c, $con);
categoryVuserPeer::setUseCriteriaFilter(true);
$sphinx = new vSphinxSearchManager();
while(count($categoryVusers))
{
	foreach($categoryVusers as $categoryVuser)
	{
	    /* @var $categoryVuser categoryVuser */
		VidiunLog::log('$categoryVuser id ' . $categoryVuser->getId() . ' updated at '. $categoryVuser->getUpdatedAt(null));
		
		try {
			$ret = $sphinx->saveToSphinx($categoryVuser, true);
		}
		catch(Exception $e){
			VidiunLog::err($e->getMessage());
			exit -1;
		}
	}
	
	$c->setOffset($c->getOffset() + count($categoryVusers));
	vMemoryManager::clearMemory();
	$categoryVusers = categoryVuserPeer::doSelect($c, $con);
}

VidiunLog::log('Done. Current time: ' . time());
exit(0);
