<?php

require_once('/opt/vidiun/app/alpha/scripts/bootstrap.php');
if ($argc < 3)
	die("Usage: php $argv[0] partnerId vusersFilePath <realrun | dryrun>"."\n");

$partnerId = $argv[1] ;
$vusersPath = $argv[2];
$dryrun = true;
if($argc == 4 && $argv[3] == 'realrun')
	$dryrun = false;
VidiunStatement::setDryRun($dryrun);
VidiunLog::debug('dryrun value: ['.$dryrun.']');
$vusers = file ($vusersPath) or die ('Could not read file'."\n");

foreach ($vusers as $vuserId)
{
	$vuserId = trim($vuserId);
	$puserIdFromVuserTable = getPuserIdFromVuserTable ($partnerId, $vuserId);
	if($puserIdFromVuserTable)
	{
		$categoryList = getCategoryListByVuser($partnerId, $vuserId);
		foreach ($categoryList as $categoryVuserItem)
		{
			$puserIdFromCategoryVuser = $categoryVuserItem->getPuserId();
			if (strcmp(trim($puserIdFromVuserTable),trim($puserIdFromCategoryVuser)))
			{
				VidiunLog::debug('vuserId ['.$vuserId.'] : update puser_id on categoryVuser table from ['.$puserIdFromCategoryVuser.'] to ['.$puserIdFromVuserTable.']');
				vCurrentContext::$partner_id = $partnerId;
				$categoryVuserItem->setPuserId($puserIdFromVuserTable);
				$categoryVuserItem->save();
			}
		}
	}
}

function getPuserIdFromVuserTable($partnerId, $vuserId)
{
	$Critiria = new Criteria();
	$Critiria->add(vuserPeer::PARTNER_ID, $partnerId);
	$Critiria->add(vuserPeer::ID, $vuserId);
	$vuser = vuserPeer::doSelectOne($Critiria);
	return $vuser->getPuserId();
}

function getCategoryListByVuser($partnerId, $vuserId)
{
	$Critiria = new Criteria();
	$Critiria->add(categoryVuserPeer::PARTNER_ID, $partnerId);
	$Critiria->add(categoryVuserPeer::VUSER_ID, $vuserId);
	return categoryVuserPeer::doSelect($Critiria);
}
