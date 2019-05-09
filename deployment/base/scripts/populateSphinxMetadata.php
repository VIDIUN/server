<?php
chdir(dirname(__FILE__));

require_once(__DIR__ . '/../../bootstrap.php');


$c = new Criteria();

if($argc > 1 && is_numeric($argv[1]))
	$c->add(MetadataPeer::UPDATED_AT, $argv[1], Criteria::GREATER_EQUAL);
if($argc > 2 && is_numeric($argv[2]))
	$c->add(MetadataPeer::PARTNER_ID, $argv[2], Criteria::EQUAL);
if($argc > 3 && is_numeric($argv[3]))
	$c->add(MetadataPeer::ID, $argv[3], Criteria::GREATER_EQUAL);
if($argc > 4)
	MetadataPeer::setUseCriteriaFilter((bool)$argv[4]);

// only dynamic objects are saved to sphinx for now
$c->addAnd(MetadataPeer::OBJECT_TYPE, MetadataObjectType::DYNAMIC_OBJECT);

$c->addAscendingOrderByColumn(MetadataPeer::UPDATED_AT);
$c->addAscendingOrderByColumn(MetadataPeer::ID);
$c->setLimit(10000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

$metadatas = MetadataPeer::doSelect($c, $con);
$sphinx = new vSphinxSearchManager();
while(count($metadatas))
{
	foreach($metadatas as $metadata)
	{
	    /* @var $metadata Metadata */
		VidiunLog::log('metadata id ' . $metadata->getId() . ' updated at '. $metadata->getUpdatedAt(null));
		
		try {
			$ret = $sphinx->saveToSphinx($metadata, true);
		}
		catch(Exception $e){
			VidiunLog::err($e->getMessage());
			exit -1;
		}
	}
	
	$c->setOffset($c->getOffset() + count($metadatas));
	vMemoryManager::clearMemory();
	$metadatas = MetadataPeer::doSelect($c, $con);
}

VidiunLog::log('Done. Curent time: ' . time());
exit(0);
