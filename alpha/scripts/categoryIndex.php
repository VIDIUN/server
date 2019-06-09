<?php
ini_set("memory_limit","256M");

require_once(__DIR__ . '/bootstrap.php');


if(!count($argv))
	die("No partner_id passed to script!");
	
$partnerId = $argv[1];

var_dump($partnerId);

if ( !PartnerPeer::retrieveByPK($partnerId) )
	die("Please enter a valid partner Id!");

$criteria = new Criteria();
$criteria->add(categoryPeer::PARTNER_ID,$partnerId,Criteria::EQUAL);
$criteria->addAscendingOrderByColumn(categoryPeer::DEPTH);
$criteria->setLimit(1000);
$allCats = categoryPeer::doSelect($criteria);

while(count($allCats))
{
	foreach ($allCats as $categoryDb)
	{
		$categoryDb->setIsIndex(true);
		
		$categoryDb->reSetFullIds();
		$categoryDb->reSetInheritedParentId();
		$categoryDb->reSetDepth();
		$categoryDb->reSetFullName();
		$categoryDb->reSetEntriesCount();
		$categoryDb->reSetMembersCount();
		$categoryDb->reSetPendingMembersCount();
		$categoryDb->reSetPrivacyContext();
		$categoryDb->reSetDirectSubCategoriesCount();
		$categoryDb->reSetDirectEntriesCount();	
		$categoryDb->save();
		
		$categoryDb->indexToSearchIndex();
	}
	
	$criteria->setOffset($criteria->getOffset() + count($allCats));
	vMemoryManager::clearMemory();
	usleep(200);
	$allCats = categoryPeer::doSelect($criteria);
}

VidiunLog::log('Done.');